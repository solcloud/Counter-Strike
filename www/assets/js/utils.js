function degreeToRadian(degree) {
    return THREE.MathUtils.degToRad(degree)
}

function radianToDegree(radian) {
    return Math.round(THREE.MathUtils.radToDeg(radian))
}

function threeHorizontalRotationToServer(eulerYXZ) {
    let horizontal = radianToDegree(eulerYXZ.y)
    const vertical = radianToDegree(eulerYXZ.x)

    if (horizontal === 0 || horizontal === -0) {
        return [0, vertical]
    }

    if (horizontal < 0) {
        return [Math.abs(horizontal), vertical]
    }

    return [360 - horizontal, vertical]
}

function serverRotationToThreeRadian(angleDegree) {
    return degreeToRadian(360 - angleDegree)
}
