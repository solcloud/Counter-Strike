function degreeToRadian(degree) {
    return THREE.MathUtils.degToRad(degree)
}

function radianToDegree(radian) {
    return Math.round(THREE.MathUtils.radToDeg(radian))
}

function threeRotationToServer(eulerYXZ) {
    const horizontal = Math.round(THREE.MathUtils.radToDeg(eulerYXZ.y) * 10) / 10
    const vertical = Math.round(THREE.MathUtils.radToDeg(eulerYXZ.x) * 10) / 10

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
