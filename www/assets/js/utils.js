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
