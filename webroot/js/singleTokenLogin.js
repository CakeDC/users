function moveToNext(current, event) {
    if (event.key === 'Backspace') {
        if (current.value.length > 0) {
            current.value = '';
            event.preventDefault();
            return;
        } else {
            const previousInput = current.previousElementSibling;
            if (previousInput) {
                previousInput.focus();
            }
            event.preventDefault();
            return;
        }
    }

    if (event.key >= '0' && event.key <= '9') {
        current.value = event.key;
        const nextInput = current.nextElementSibling;
        if (nextInput) {
            nextInput.focus();
        }
        event.preventDefault();
    } else {
        event.preventDefault();
    }
    checkAndSubmit();
}

function checkAndSubmit() {
    const inputs = document.querySelectorAll('.token-input');
    const allFilled = Array.from(inputs).every(input => input.value.length === 1);

    if (allFilled) {
        document.querySelector('form').submit();
    }
}
document.querySelectorAll('.token-input').forEach(input => {
    input.addEventListener('paste', function(event) {
        const pasteData = event.clipboardData.getData('text');
        const digits = pasteData.match(/\d/g);

        if (digits && digits.length === 6) {
            const inputs = document.querySelectorAll('.token-input');
            inputs.forEach((input, index) => {
                if (index < digits.length) {
                    input.value = digits[index];
                } else {
                    input.value = '';
                }
            });
            const lastInput = document.querySelector('#token-5');
            if (lastInput) {
                lastInput.focus();
            }

            checkAndSubmit();
        }
        event.preventDefault();
    });

    input.addEventListener('input', function(event) {
        if (event.inputType === "deleteContentBackward") {
            return;
        }
        if (event.inputType === "insertText" && this.value.length >= 1) {
            event.preventDefault();
            return;
        }

        if (!/^\d$/.test(this.value)) {
            event.preventDefault();
            this.value = '';
            return;
        }

        const nextInput = this.nextElementSibling;
        if (nextInput) {
            if (nextInput) {
                nextInput.focus();
            }
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const firstInput = document.querySelector('#token-0');
    if (firstInput) {
        firstInput.focus();
    }
});
