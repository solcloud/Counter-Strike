function degreeToRadian(degree) {
    return THREE.MathUtils.degToRad(degree)
}

function radianToDegree(radian) {
    return Math.round(THREE.MathUtils.radToDeg(radian))
}

function threeRotationToServer(eulerYXZ) {
    const horizontal = THREE.MathUtils.radToDeg(eulerYXZ.y)
    const vertical = THREE.MathUtils.radToDeg(eulerYXZ.x)

    if (horizontal === 0) {
        return [0, vertical]
    }

    if (horizontal < 0) {
        return [Math.abs(horizontal), vertical]
    }

    return [360 - horizontal, vertical]
}

function serverHorizontalRotationToThreeRadian(angleDegree) {
    return degreeToRadian(360 - angleDegree)
}

function serverVerticalRotationToThreeRadian(angleDegree) {
    return degreeToRadian(angleDegree)
}

function msToTick(timeMs) {
    return Math.ceil(timeMs / window._csfGlobal.tickMs)
}

function smallestDeltaAngle(start, target) {
    const a = ((start - target) % 360 + 360) % 360;
    const b = ((target - start) % 360 + 360) % 360;
    return (a < b ? -a : b);
}

function rotatePointY(angle, x, z, centerX = 0, centerZ = 0, clockWise = true) {
    let sin = Math.sin(degreeToRadian(angle));
    let cos = Math.cos(degreeToRadian(angle));

    return [
        centerX + cos * (x - centerX) + sin * (z - centerZ),
        centerZ + (clockWise ? -1 : 1) * sin * (x - centerX) + cos * (z - centerZ),
    ];
}
