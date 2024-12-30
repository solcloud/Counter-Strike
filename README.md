# Counter-Strike: Football [![Tests](https://github.com/solcloud/Counter-Strike/actions/workflows/test.yml/badge.svg)](https://github.com/solcloud/Counter-Strike/actions/workflows/test.yml) [![Code coverage](https://img.shields.io/badge/Code%20coverage-100%25-green?style=flat)](https://github.com/solcloud/Counter-Strike/actions/workflows/test.yml) [![Mutation score](https://img.shields.io/badge/Mutation%20score-100%25-green?style=flat)](https://github.com/solcloud/Counter-Strike/actions/workflows/test.yml)

Competitive multiplayer FPS game where two football fan teams fight with the goal of winning more rounds than the opponent team.

![promo](https://github.com/solcloud/Counter-Strike/assets/74121353/dfca8ed0-4624-4199-8d4c-336e101e0922)

Teams are Attackers and Defenders. The Defenders team has a goal of protecting their fan base sanctuary every round from desecrating by attackers using their graffiti bomb ball.

Defenders win round by:
- eliminated all attackers players before bomb planted (touchdown)
- defusing bomb before it blows (graffiti fireworks)
- at least one player survive round time and no bomb is planted

Attackers win round by:
- eliminated all defenders players before round time ends
- planted bomb (touchdown) before round time ends and don't allow defenders to defuse it

If attackers deploy graffiti bomb before round time ends, then round clock countdown is set to 40 sec and defenders has 30 sec (or 35 sec in case of using defuse kit) to defuse bomb.

_This is low violence game so there is no red blood, chickens killing or similar violence visuals._

## Setup

### Client

Download executable asset for your OS platform from the [latest release](https://github.com/solcloud/Counter-Strike/releases/latest). Or build by yourself locally from the project source folder.

```bash
cd electron/
npm install
npm run dev
```

### Server

Currently, there is no official public server available (as match making service is also WIP), but you can run server yourself (or somebody can host it for you).

```bash
composer install -a --no-dev
php cli/server.php 2 # will start server waiting for 2 players to connect
```

## Help us

If you know html/css/js/php languages or 3D modeling/texturing/animation you can join the project and help us improve this game quicker by sending a pull request.
