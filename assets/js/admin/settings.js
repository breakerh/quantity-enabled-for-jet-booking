(function() {
    const switchers = document.querySelectorAll('.cx-vui-switcher');
    switchers.forEach(function(switcher) {
        switcher.addEventListener('click', function() {
            const input = switcher.querySelector('input.cx-vui-switcher__input');
            if (switcher.classList.contains('cx-vui-switcher--on')) {
                switcher.classList.remove('cx-vui-switcher--on');
                switcher.classList.add('cx-vui-switcher--off');
                input.value = 'off';
            } else {
                switcher.classList.remove('cx-vui-switcher--off');
                switcher.classList.add('cx-vui-switcher--on');
                input.value = 'on';
            }
            setTimeout(() => {
                checkIfInputsHaveChanged();
            }, 1000);
        });
    });

    let initialValues = Array.from(document.querySelectorAll('input')).map(input => {
        return {
            name: input.name,
            value: input.value,
        };
    })
    function checkIfInputsHaveChanged() {
        const currentValues = Array.from(document.querySelectorAll('input')).map(input => {
            return {
                name: input.name,
                value: input.value,
            };
        })
        let changed = false;
        for (let i = 0; i < initialValues.length; i++) {
            if (initialValues[i].value !== currentValues[i].value) {
                changed = true;
                break;
            }
        }
        if (changed) {
            initialValues = currentValues;
            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'jet_abaf_qefjb_save_settings',
                    settings: currentValues.map(input => `${input.name}=${input.value}`).join('&'),
                })
            })
                .then(response => response.json())
                .then(data => {
                    initialValues = currentValues;
                    const successMessage = document.createElement('div');
                    successMessage.innerHTML = `
                        <div class="cx-vui-notice cx-vui-notice--success">
                            <div class="cx-vui-notice__icon cx-vui-notice__icon--success">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M6.38498 12.0188L13.5962 4.80751L12.4695 3.64319L6.38498 9.7277L3.53052 6.87324L2.40376 8L6.38498 12.0188ZM2.32864 2.3662C3.9061 0.788732 5.79656 0 8 0C10.2034 0 12.0814 0.788732 13.6338 2.3662C15.2113 3.91862 16 5.79656 16 8C16 10.2034 15.2113 12.0939 13.6338 13.6714C12.0814 15.2238 10.2034 16 8 16C5.79656 16 3.9061 15.2238 2.32864 13.6714C0.776213 12.0939 0 10.2034 0 8C0 5.79656 0.776213 3.91862 2.32864 2.3662Z"></path>
                                </svg>
                            </div>
                            <div class="cx-vui-notice__content">
                                <div class="cx-vui-notice__message">Settings saved!</div>
                            </div>
                            <div class="cx-vui-notice__close">
                                <svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M16 1.61074L9.61074 8L16 14.3893L14.3893 16L8 9.61074L1.61074 16L0 14.3893L6.38926 8L0 1.61074L1.61074 0L8 6.38926L14.3893 0L16 1.61074Z"></path>
                                </svg>
                            </div>
                        </div>
                    `;
                    document.querySelector('.cx-vui-notices > div').appendChild(successMessage);
                    successMessage.querySelector('.cx-vui-notice__close').addEventListener('click', () => successMessage.remove());
                    setTimeout(() => successMessage?.remove(), 5000);
                })
                .catch((error) => {
                    console.error('Error:', error);
                });
        }
    }

    let timeout = null;
    document.querySelectorAll('.jet-abaf-wrap input').forEach(input => {
        input.addEventListener('input', () => {
            clearTimeout(timeout);
            timeout = setTimeout(checkIfInputsHaveChanged, 1000);
            //initialValues = Array.from(document.querySelectorAll('input')).map(input => input.value);
        });
    });
}());
