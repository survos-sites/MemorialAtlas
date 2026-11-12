"""Exercise the installer without starting services or changing this checkout."""
import os
from pathlib import Path
import subprocess
import tempfile

script = (Path(__file__).resolve().parents[1] / 'bin/install.sh').read_text()
with tempfile.TemporaryDirectory(prefix='memorialatlas-install-') as directory:
    root = Path(directory)
    commands = root / 'commands'
    commands.mkdir()
    log = root / 'calls'
    env = dict(os.environ, PATH=str(commands) + ':' + os.environ['PATH'], INSTALL_TEST_LOG=str(log), INSTALL_TEST_EXAMPLE=str(Path(__file__).resolve().parents[1] / '.env.local.example'))
    scripts = {
        'git': '''mkdir -p "${@: -1}/bin"
 touch "${@: -1}/docker-compose.yaml"
 cp "$INSTALL_TEST_EXAMPLE" "${@: -1}/.env.local.example"
 printf '#!/usr/bin/env bash\\necho load-data >> "$INSTALL_TEST_LOG"\\nexit "${INSTALL_TEST_LOAD_EXIT:-0}"\\n' > "${@: -1}/bin/load-data"
 chmod +x "${@: -1}/bin/load-data"
''',
        'php': '''if [[ "$*" == *check-local-ports.php* ]]; then exit "${INSTALL_TEST_PORT_EXIT:-0}"; fi
if [[ "$*" == *random_bytes* ]]; then printf 'test-secret'; fi
''',
        'composer': 'exit "${INSTALL_TEST_COMPOSER_EXIT:-0}"\n',
        'docker': 'if [[ "$1" == info ]]; then exit "${INSTALL_TEST_RUNTIME_EXIT:-0}"; fi\n',
        'symfony': 'if [[ "$*" == "console app:startup" ]]; then exit "${INSTALL_TEST_LOAD_EXIT:-0}"; fi\n',
    }
    for name, body in scripts.items():
        path = commands / name
        path.write_text('#!/usr/bin/env bash\nprintf "%s\\n" "' + name + ' $*" >> "$INSTALL_TEST_LOG"\n' + body)
        path.chmod(0o755)

    def run(target, *args, overrides=None):
        return subprocess.run(['bash', '-s', '--', '--dir', str(target), *args], input=script,
                              text=True, capture_output=True, env=env | (overrides or {}), cwd=root)

    target = root / 'new checkout with spaces'
    result = run(target)
    assert result.returncode == 0, result.stderr
    calls = log.read_text()
    assert 'compose --profile standalone --profile search up -d --wait' in calls
    assert calls.index('symfony console app:startup') < calls.index('symfony console app:processing:status')
    assert 'elastic:index:rebuild' not in calls
    assert calls.index('symfony server:start -d') < calls.index('symfony open:local')
    assert calls.strip().endswith('symfony open:local')
    assert 'The Hugging Face demo is already loaded' in result.stdout
    config = target / '.env.local'
    assert config.stat().st_mode & 0o077 == 0
    original = config.read_bytes()
    for key in ['MISTRAL_API_KEY', 'TYPESAFE_API_KEY', 'OPENAI_API_KEY', 'HF_TOKEN', 'AWS_S3_ACCESS_SECRET']:
        assert f'{key}=\n' in config.read_text()
    assert config.read_text().count('APP_SECRET=') == 1
    assert config.read_text().count('DATABASE_URL=') == 1
    assert run(target).returncode == 0
    assert config.read_bytes() == original

    config.write_text('APP_SECRET=keep-me\nDATABASE_URL=existing\n')
    log.write_text('')
    assert run(target).returncode == 0
    assert config.read_text() == 'APP_SECRET=keep-me\nDATABASE_URL=existing\n'
    assert 'docker ' not in log.read_text()

    for overrides in [{'INSTALL_TEST_COMPOSER_EXIT': '1'}, {'INSTALL_TEST_LOAD_EXIT': '1'}]:
        log.write_text('')
        assert run(target, overrides=overrides).returncode != 0
        assert 'symfony server:start' not in log.read_text()

    installer = root / 'install.sh'
    installer.write_text(script)
    parent = subprocess.run(['bash', '-c', 'cat "$1" | bash -s -- --dir "$2" && cd "$2" && pwd',
                             'test', str(installer), str(target)], text=True, capture_output=True, env=env, cwd=root)
    assert parent.returncode == 0, parent.stderr
    assert parent.stdout.strip().endswith(str(target))

    assert run(root / 'requires configuration', '--existing-services').returncode != 0
    assert not (root / 'requires configuration' / '.env.local').exists()
    log.write_text('')
    assert run(root / 'conflicting ports', overrides={'INSTALL_TEST_PORT_EXIT': '1'}).returncode != 0
    assert 'composer install' not in log.read_text()
    assert ' up -d ' not in log.read_text()
    log.write_text('')
    stop = subprocess.run(['bash', str(Path(__file__).resolve().parents[1] / 'bin/stop')], text=True, capture_output=True, env=env)
    assert stop.returncode == 0, stop.stderr
    assert 'symfony server:stop' in log.read_text()
    assert 'docker compose --profile standalone --profile search down' in log.read_text()
    assert '--volumes' not in log.read_text()

    log.write_text('')
    runtime_target = root / 'stopped runtime'
    result = run(runtime_target, overrides={'INSTALL_TEST_RUNTIME_EXIT': '1'})
    assert result.returncode != 0
    assert 'podman machine start' in result.stderr
    assert 'composer install' not in log.read_text()
    assert not (runtime_target / '.env.local').exists()
    runtime_target.joinpath('.env.local').write_text('# MemorialAtlas installer: managed local services\n')
    log.write_text('')
    assert run(runtime_target, overrides={'INSTALL_TEST_RUNTIME_EXIT': '1'}).returncode != 0
    assert 'composer install' not in log.read_text()
    log.write_text('')
    wrapper = subprocess.run(['sh', str(Path(__file__).resolve().parents[1] / 'bin/load-data')], env=env)
    assert wrapper.returncode == 0
    assert 'symfony console app:load' in log.read_text()

    occupied = root / 'unrelated'
    occupied.mkdir()
    assert run(occupied).returncode != 0
    assert not list(occupied.iterdir())
    print('Installer: piped fresh install, spaced path, private config, idempotency, existing services and failure stops passed.')
