export class Utils {

    static tickMs = 0
    static LAYER_ALL = 0
    static LAYER_WORLD = 1
    static LAYER_ITEMS = 2
    static LAYER_PLAYERS = 3

    static degreeToRadian(degree) {
        return degree * Math.PI / 180
    }

    static radianToDegree(radian) {
        return Math.round(radian * 180 / Math.PI)
    }

    static threeRotationToServer(eulerYXZ) {
        const horizontal = eulerYXZ.y * 180 / Math.PI
        const vertical = eulerYXZ.x * 180 / Math.PI

        if (horizontal === 0) {
            return [0, vertical]
        }

        if (horizontal < 0) {
            return [Math.abs(horizontal), vertical]
        }

        return [360 - horizontal, vertical]
    }

    static serverHorizontalRotationToThreeRadian(angleDegree) {
        return this.degreeToRadian(360 - angleDegree)
    }

    static serverVerticalRotationToThreeRadian(angleDegree) {
        return this.degreeToRadian(angleDegree)
    }

    static scopeLevelToZoom(scopeLevel) {
        if (!scopeLevel) {
            return 1
        }
        if (scopeLevel === 1) {
            return 4
        }
        if (scopeLevel === 2) {
            return 15
        }

        return 99
    }

    static randomInt(start, end) {
        return start + Math.floor(Math.random() * (end - start + 1));
    }

    static lerp(start, end, percentage) {
        return (1 - percentage) * start + percentage * end;
    }

    static msToTick(timeMs) {
        return Math.ceil(timeMs / this.tickMs)
    }

    static smallestDeltaAngle(start, target) {
        const a = ((start - target) % 360 + 360) % 360;
        const b = ((target - start) % 360 + 360) % 360;
        return (a < b ? -a : b);
    }

    static rotatePointY(angle, x, z, centerX = 0, centerZ = 0, clockWise = true) {
        let sin = Math.sin(this.degreeToRadian(angle));
        let cos = Math.cos(this.degreeToRadian(angle));

        return [
            centerX + cos * (x - centerX) + sin * (z - centerZ),
            centerZ + (clockWise ? -1 : 1) * sin * (x - centerX) + cos * (z - centerZ),
        ];
    }
}
