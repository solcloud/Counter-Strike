(function () {

    const _PI_2 = Math.PI / 2;
    const _euler = new THREE.Euler(0, 0, 0, 'YXZ');
    const _vector = new THREE.Vector3();

    class PointerLockControls {

        constructor(camera, domElement) {
            const scope = this;
            this.domElement = domElement;
            this.isLocked = false; // Set to constrain the pitch of the camera

            // Range is 0 to Math.PI radians
            this.minPolarAngle = 0; // radians
            this.maxPolarAngle = Math.PI; // radians

            this.pointerSpeed = 1.0;

            function onMouseMove(event) {
                if (scope.isLocked === false) {
                    return
                }

                const movementX = event.movementX || event.mozMovementX || event.webkitMovementX || 0;
                const movementY = event.movementY || event.mozMovementY || event.webkitMovementY || 0;

                _euler.setFromQuaternion(camera.quaternion);
                _euler.y -= movementX * 0.002 * scope.pointerSpeed;
                _euler.x -= movementY * 0.002 * scope.pointerSpeed;
                _euler.x = Math.max(_PI_2 - scope.maxPolarAngle, Math.min(_PI_2 - scope.minPolarAngle, _euler.x));

                camera.quaternion.setFromEuler(_euler);
            }

            function onPointerlockChange() {
                if (scope.domElement.ownerDocument.pointerLockElement === scope.domElement) {
                    scope.isLocked = true;
                } else {
                    scope.isLocked = false;
                }

            }

            function onPointerlockError() {
                console.error('THREE.PointerLockControls: Unable to use Pointer Lock API');
            }

            this.connect = function () {
                scope.domElement.ownerDocument.addEventListener('mousemove', onMouseMove);
                scope.domElement.ownerDocument.addEventListener('pointerlockchange', onPointerlockChange);
                scope.domElement.ownerDocument.addEventListener('pointerlockerror', onPointerlockError);
            };

            this.disconnect = function () {
                scope.domElement.ownerDocument.removeEventListener('mousemove', onMouseMove);
                scope.domElement.ownerDocument.removeEventListener('pointerlockchange', onPointerlockChange);
                scope.domElement.ownerDocument.removeEventListener('pointerlockerror', onPointerlockError);
            };

            this.dispose = function () {
                this.disconnect();
            };

            this.getObject = function () {
                // retaining this method for backward compatibility
                return camera;

            };

            this.getDirection = function () {
                const direction = new THREE.Vector3(0, 0, -1);
                return function (v) {
                    return v.copy(direction).applyQuaternion(camera.quaternion);
                };
            }();

            this.moveForward = function (distance) {
                // move forward parallel to the xz-plane
                // assumes camera.up is y-up
                _vector.setFromMatrixColumn(camera.matrix, 0);
                _vector.crossVectors(camera.up, _vector);
                camera.position.addScaledVector(_vector, distance);

            };

            this.moveRight = function (distance) {
                _vector.setFromMatrixColumn(camera.matrix, 0);
                camera.position.addScaledVector(_vector, distance);
            };

            this.lock = function () {
                this.domElement.requestPointerLock();
            };

            this.unlock = function () {
                scope.domElement.ownerDocument.exitPointerLock();
            };

            this.reset = function () {
                _euler.setFromQuaternion(camera.quaternion)
                _vector.set(0, 0, 0)
            }

            this.connect();
        }

    }

    THREE.PointerLockControls = PointerLockControls;

})();
