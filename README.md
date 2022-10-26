# Counter-Strike: Football [![Tests](https://github.com/solcloud/Counter-Strike/actions/workflows/test.yml/badge.svg)](https://github.com/solcloud/Counter-Strike/actions/workflows/test.yml)

Competitive multiplayer game where two football fans teams, each team have 5 players, fights in 30 rounds with goal to win more rounds than opponents team.
Teams are Attackers and Defenders. Defenders team has goal of protecting their fan base sanctuary every round from desecrate by attackers using their graffiti bomb ball.

![week1](https://user-images.githubusercontent.com/74121353/190862209-653e8339-acb1-4feb-ac08-93bcdd944272.png)

Defenders win round by:
- eliminated all attackers players before bomb planted (touchdown)
- defusing bomb before it blows (graffiti fireworks)
- at least one player survive round time and no bomb is planted

Attackers win round by:
- eliminated all defenders players before round time ends
- planted bomb (touchdown) before round time ends and don't allow defenders to defuse it

If attackers deploy graffiti bomb before round time ends, then round clock countdown is set to 40 sec and defenders has 30 sec (or 35 sec in case of using defuse kit) to defuse bomb.

This is NO violence game so there is no blood or violence visuals.

## Setup

Currently, there is no official public server available (as match making service is also WIP), but you can run server locally (or somebody can host it for you).

```bash
git clone https://github.com/solcloud/Counter-Strike cs-football
cd cs-football
composer install --no-dev
php cli/server.php 2 # will start server waiting for 2 players to connect
```

For connecting using Web Browser you need WebSocket UDP bridge

```bash
php cli/udp-ws-bridge.php 8081 # will start WebSocket listener on localhost:8081
```

For better performance (no UDP bridge required) download Electron app for your platform from the [latest release](https://github.com/solcloud/Counter-Strike/releases/latest), or run locally

```bash
cd electron/
npm install
npm run dev
```

### NOT going to implement
- burst fire
- guns with absurd fire rate (M249, Negev, duals, ...)
- different players model (same hit boxes for all players, only design change)
- player breath animation or any player animation beside walking, crouching, jumping and look movement
- lag compensation - when you lag it is your problem - your disadvantage

### To Be Decided (maybe)
- aim punch (probably only headshot and body shot with no kevlar)
- aim spread
- gun/util hit boxes (maybe only when dropped)
- backtrack (max only few ticks like 2)
