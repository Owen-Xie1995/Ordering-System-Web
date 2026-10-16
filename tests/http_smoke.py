"""Run against an explicitly selected disposable MySQL database ending in _test.

Uses only Python's standard library. It starts its own PHP server and sessions,
creates uniquely named test accounts, then removes them and restores stock.
"""
import http.cookiejar
import json
import os
from pathlib import Path
import re
import secrets
import socket
import subprocess
import tempfile
import time
import urllib.error
import urllib.parse
import urllib.request

ROOT = Path(__file__).resolve().parents[1]
PHP = os.environ.get("PHP_BINARY", "php")
if not os.environ.get("DB_NAME", "").endswith("_test"):
    raise SystemExit("Set DB_NAME to an explicitly disposable database ending in _test.")
if os.environ.get("ALLOW_TEST_DB") != "1":
    raise SystemExit("Set ALLOW_TEST_DB=1 to confirm this is a disposable database.")

def php(code, *args, env=None):
    return subprocess.check_output([PHP, "-r", code, *map(str, args)], cwd=ROOT, env=env, text=True)

def sql(query, params=()):
    code = """require $argv[1]; $s=db()->prepare($argv[2]);
$p=json_decode($argv[3],true); if($p) $s->bind_param(str_repeat('s',count($p)),...$p);
$s->execute(); $r=$s->get_result(); echo json_encode($r ? $r->fetch_all(MYSQLI_ASSOC) : []);"""
    return json.loads(php(code, ROOT / "includes/app.php", query, json.dumps(list(params))))

class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, request, fp, code, msg, headers, newurl):
        return None

class Client:
    def __init__(self, base):
        self.base = base
        self.cookies = http.cookiejar.CookieJar()
        self.opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(self.cookies), NoRedirect())

    def request(self, path, data=None):
        request = urllib.request.Request(self.base + path, None if data is None else urllib.parse.urlencode(data).encode())
        try:
            response = self.opener.open(request, timeout=5)
        except urllib.error.HTTPError as error:
            response = error
        return response.status, response.headers, response.read()

    def session(self):
        status, _, body = self.request("/session.php")
        assert status == 200, (status, body)
        return json.loads(body)

    def login(self, account, password, session_dir):
        token = self.session()["csrf"]
        before = next(cookie.value for cookie in self.cookies if cookie.name == "PHPSESSID")
        assert self.request("/captcha.php")[0] == 200
        # Inspect only this runner's private session directory, never expose a
        # test-only endpoint that reveals CAPTCHA values over HTTP.
        state = json.loads(php('session_save_path($argv[1]); session_id($argv[2]); session_start(); echo json_encode($_SESSION);', session_dir, before))
        result = self.request("/login.php", {"csrf": token, "a": account, "pw": password, "captcha": state["captcha"]["code"]})
        return before, result

prefix = "test_" + secrets.token_hex(5)
member_id, admin_id, legacy_id = (prefix + suffix for suffix in ("_member", "_admin", "_legacy"))
password = "Portfolio-test-2026"
server = None
original_stock = None
subprocess.run([PHP, "scripts/setup.php"], cwd=ROOT, check=True)
try:
    original_stock = sql("SELECT quantity FROM inventory WHERE id = 1")[0]["quantity"]
    with tempfile.TemporaryDirectory(prefix="ordering-http-") as temporary:
        sessions = Path(temporary) / "sessions"
        sessions.mkdir()
        with socket.socket() as probe:
            probe.bind(("127.0.0.1", 0))
            port = probe.getsockname()[1]
        log = open(Path(temporary) / "server.log", "w+")
        server = subprocess.Popen([PHP, "-d", "session.save_path=" + str(sessions), "-d", "display_errors=0", "-S", f"127.0.0.1:{port}", "router.php"], cwd=ROOT, stdout=log, stderr=log)
        base = f"http://127.0.0.1:{port}"
        anonymous = Client(base)
        for _ in range(50):
            try:
                anonymous.session()
                break
            except (OSError, urllib.error.URLError):
                time.sleep(0.1)
        else:
            raise AssertionError("PHP server did not start")

        assert anonymous.request("/admin.php")[0] == 303
        assert anonymous.request("/scripts/setup.php")[0] == 404
        assert anonymous.request("/database/schema.sql")[0] == 404
        assert anonymous.request("/.env.example")[0] == 404
        assert anonymous.request("/register.php", {"id": member_id, "pw": password})[0] == 403
        token = anonymous.session()["csrf"]
        assert anonymous.request("/reg.php", {"csrf": token, "id": "x' OR 1=1 --", "pw": password})[0] == 422
        assert anonymous.request("/register.php", {"csrf": token, "id": member_id, "pw": password, "role": "admin"})[0] == 303
        stored = sql("SELECT pw, role FROM login WHERE id = ?", [member_id])[0]
        assert stored["pw"] != password and stored["pw"].startswith("$2")
        assert stored["role"] == "member"
        assert anonymous.request("/reg.php", {"csrf": token, "id": member_id, "pw": password})[0] == 422

        member = Client(base)
        before, result = member.login(member_id, password, sessions)
        assert result[0] == 303 and result[1]["Location"] == "home.php", result
        after = next(cookie.value for cookie in member.cookies if cookie.name == "PHPSESSID")
        assert before != after, "Session ID did not rotate"
        assert member.session()["authenticated"] and not member.session()["is_admin"]
        assert member.request("/admin.php")[0] == 403
        assert member.request("/admin.php", {"csrf": member.session()["csrf"], "id": 1, "quantity": 999})[0] == 403

        injected = Client(base)
        assert injected.login(member_id + "' OR '1'='1", password, sessions)[1][0] == 422
        assert not injected.session()["authenticated"]

        environment = dict(os.environ, ADMIN_PASSWORD=password)
        subprocess.run([PHP, "scripts/create_admin.php", admin_id], cwd=ROOT, env=environment, check=True)
        admin = Client(base)
        assert admin.login(admin_id, password, sessions)[1][0] == 303
        assert admin.session()["is_admin"]
        assert admin.request("/admin.php")[0] == 200
        assert admin.request("/admin.php", {"id": 1, "quantity": 73})[0] == 403
        admin_token = admin.session()["csrf"]
        for bad in ("-1", "1.5", "1000000000"):
            assert admin.request("/admin.php", {"csrf": admin_token, "id": 1, "quantity": bad})[0] == 422
        updated = admin.request("/admin.php", {"csrf": admin_token, "id": 1, "quantity": 73})
        assert updated[0] == 303 and updated[1]["Location"] == "admin.php?updated=1"
        assert int(sql("SELECT quantity FROM inventory WHERE id = 1")[0]["quantity"]) == 73
        assert admin.request("/logout.php")[0] == 405
        assert admin.session()["authenticated"]
        assert admin.request("/logout.php", {"csrf": "wrong"})[0] == 403
        assert admin.request("/logout.php", {"csrf": admin_token})[0] == 303
        assert not admin.session()["authenticated"]
        assert admin.request("/admin.php")[0] == 303

        sql("INSERT INTO login (id, pw) VALUES (?, ?)", [legacy_id, "oldpass"])
        subprocess.run([PHP, "scripts/migrate_legacy.php"], cwd=ROOT, check=True)
        assert sql("SELECT pw FROM login WHERE id = ?", [legacy_id])[0]["pw"] == "oldpass", "Dry-run mutated credentials"
        subprocess.run([PHP, "scripts/migrate_legacy.php", "--apply"], cwd=ROOT, check=True)
        assert sql("SELECT pw FROM login WHERE id = ?", [legacy_id])[0]["pw"] != "oldpass"
        assert Client(base).login(legacy_id, "oldpass", sessions)[1][0] == 303
        assert sql("SELECT pw FROM login WHERE id = ?", [member_id])[0]["pw"] == stored["pw"], "Existing hash was double-hashed"
        print("HTTP integration passed: registration, alternate route, password migration, session rotation, SQL input, admin authorization, CSRF, stock validation, logout.")
finally:
    if server is not None:
        server.terminate()
        server.wait(timeout=5)
    if original_stock is not None:
        sql("UPDATE inventory SET quantity = ? WHERE id = 1", [original_stock])
    for account in (member_id, admin_id, legacy_id):
        sql("DELETE FROM login WHERE id = ?", [account])
