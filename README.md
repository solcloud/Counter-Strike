# Counter-Strike: Football [![Tests](https://github.com/solcloud/Counter-Strike/actions/workflows/test.yml/badge.svg)](https://github.com/solcloud/Counter-Strike/actions/workflows/test.yml)

Competitive multiplayer FPS game where two football fan teams fight with the goal of winning more rounds than the opponent team.

![promo](https://user-images.githubusercontent.com/74121353/210066609-3a691c0d-202b-4634-9f49-d1f6ff3538c8.png)

Teams are Attackers and Defenders. The Defenders team has a goal of protecting their fan base sanctuary every round from desecrating by attackers using their graffiti bomb ball.

Defenders win round by:
- eliminated all attackers players before bomb planted (touchdown)
- defusing bomb before it blows (graffiti fireworks)
- at least one player survive round time and no bomb is planted

Attackers win round by:
- eliminated all defenders players before round time ends
- planted bomb (touchdown) before round time ends and don't allow defenders to defuse it

If attackers deploy graffiti bomb before round time ends, then round clock countdown is set to 40 sec and defenders has 30 sec (or 35 sec in case of using defuse kit) to defuse bomb.

_This is low violence game so there is no red blood, animal killing or similar violence visuals._

## Setup

### Client

Download executable binary for your platform from the [latest release](https://github.com/solcloud/Counter-Strike/releases/latest), or build latest locally from project source folder.

```bash
cd electron/
npm install
npm run dev
```

Or you can play using modern Web Browser - just open `www/index.html` in your favorite browser, but for that you will also need WebSocket UDP bridge.

```bash
php cli/udp-ws-bridge.php 8081 # will start WebSocket listener on localhost:8081
```

CORS policy might require web server running.

```bash
php -S localhost:9000 -t www/
open http://localhost:9000
```

### Server

Currently, there is no official public server available (as match making service is also WIP), but you can run server yourself (or somebody can host it for you).

```bash
composer install -a --no-dev
php cli/server.php 2 # will start server waiting for 2 players to connect
```

Enabling PHP's JIT compilation should give a performance boost, so we recommend doing that.

## Help us

If you know html/css/js/php languages or 3D modeling/texturing/animation you can join project and help us improve this game quicker.
