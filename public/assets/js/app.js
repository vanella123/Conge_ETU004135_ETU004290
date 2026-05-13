/**
 * app.js — Shared frontend logic (frontoffice)
 * Confirm modal, AJAX forms, mobile menu toggle.
 */
(function () {
    /* ── Mobile Menu Toggle ── */
    const menuBtn = document.querySelector('.mobile-menu-btn');
    const nav = document.querySelector('.nav');
    if (menuBtn && nav) {
        menuBtn.addEventListener('click', function () {
            nav.classList.toggle('is-open');
        });
    }

    /* ── Form field helpers ── */
    const resetFieldErrors = (form) => {
        form.querySelectorAll('.field-error').forEach((node) => {
            node.textContent = '';
        });
        form.querySelectorAll('.is-invalid').forEach((node) => {
            node.classList.remove('is-invalid');
        });
    };

    const setFeedback = (form, type, message, errors = {}) => {
        const feedback = form.querySelector('[data-form-feedback]');
        if (!feedback) return;

        feedback.className = `form-feedback is-visible ${type}`;
        const errorEntries = Object.entries(errors || {});
        if (type === 'error' && errorEntries.length) {
            const unique = [...new Set(errorEntries.map(([, value]) => String(value)))].filter((value) => value !== String(message || ''));
            const list = unique.map((value) => `<li>${value}</li>`).join('');
            feedback.innerHTML = list
                ? `<strong>${message}</strong><ul class="error-list">${list}</ul>`
                : `<strong>${message}</strong>`;
        } else {
            feedback.textContent = message || '';
        }
    };

    const showFieldErrors = (form, errors = {}) => {
        Object.entries(errors || {}).forEach(([fieldName, errorMessage]) => {
            const errorNode = form.querySelector(`[data-field-error="${fieldName}"]`);
            const inputNode = form.querySelector(`[name="${fieldName}"]`);
            if (errorNode) errorNode.textContent = errorMessage;
            if (inputNode) inputNode.classList.add('is-invalid');
        });
    };

    const setLoading = (submit, loading) => {
        if (!submit) return;
        if (loading) {
            submit.dataset.originalText = submit.tagName.toLowerCase() === 'button' ? submit.textContent : submit.value;
            if (submit.tagName.toLowerCase() === 'button') {
                submit.textContent = 'Traitement...';
            } else {
                submit.value = 'Traitement...';
            }
            submit.disabled = true;
        } else {
            const original = submit.dataset.originalText;
            if (original) {
                if (submit.tagName.toLowerCase() === 'button') {
                    submit.textContent = original;
                } else {
                    submit.value = original;
                }
            }
            submit.disabled = false;
        }
    };

    /* ── Confirm Modal ── */
    const modal = document.getElementById('confirm-modal');
    const modalMessage = document.getElementById('confirm-message');
    const modalOk = document.getElementById('confirm-ok');
    const modalCancel = document.getElementById('confirm-cancel');
    let pendingAction = null;

    const closeModal = () => {
        if (!modal) return;
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
        pendingAction = null;
    };

    modalCancel?.addEventListener('click', closeModal);
    modal?.addEventListener('click', (event) => {
        if (event.target === modal) closeModal();
    });
    modalOk?.addEventListener('click', () => {
        if (!pendingAction) return closeModal();
        const action = pendingAction;
        closeModal();
        if (action.type === 'link') {
            window.location.href = action.href;
        } else if (action.type === 'submit') {
            if (typeof action.form.requestSubmit === 'function') {
                action.form.requestSubmit(action.submitter || undefined);
            } else if (action.submitter) {
                action.submitter.click();
            } else {
                action.form.submit();
            }
        }
    });

    document.querySelectorAll('[data-confirm-message]').forEach((element) => {
        element.addEventListener('click', function (event) {
            event.preventDefault();
            const message = this.getAttribute('data-confirm-message') || 'Confirmer cette action ?';
            if (modalMessage) modalMessage.textContent = message;
            if (modal) {
                modal.classList.add('open');
                modal.setAttribute('aria-hidden', 'false');
            }
            if (this.tagName.toLowerCase() === 'a') {
                pendingAction = { type: 'link', href: this.getAttribute('href') };
            } else if (this.type === 'submit' && this.form) {
                pendingAction = { type: 'submit', form: this.form, submitter: this };
            }
        });
    });

    /* ── Form Handling ── */
    document.querySelectorAll('form').forEach((form) => {
        if (form.dataset.ajaxForm !== 'true') {
            form.addEventListener('submit', function () {
                const submit = this.querySelector('button[type="submit"], input[type="submit"]');
                if (!submit) return;
                const original = submit.dataset.originalText || submit.textContent || submit.value || 'Envoyer';
                submit.dataset.originalText = original;
                if (submit.tagName.toLowerCase() === 'button') {
                    submit.textContent = 'Traitement...';
                } else {
                    submit.value = 'Traitement...';
                }
                submit.disabled = true;
            });
            return;
        }

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const submit = form.querySelector('button[type="submit"], input[type="submit"]');
            resetFieldErrors(form);
            setFeedback(form, 'error', '', {});
            setLoading(submit, true);

            try {
                const response = await fetch(form.action, {
                    method: (form.method || 'POST').toUpperCase(),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: new FormData(form),
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    setFeedback(form, 'error', data.message || 'Veuillez vérifier les champs du formulaire.', data.errors || {});
                    const errors = data.errors || {};
                    const messages = Object.values(errors).map((v) => String(v));
                    const allSameAsTop = messages.length > 0 && messages.every((m) => m === String(data.message || ''));
                    if (!allSameAsTop) {
                        showFieldErrors(form, errors);
                    }
                    setLoading(submit, false);
                    return;
                }

                if (data.message) {
                    setFeedback(form, 'success', data.message);
                }

                if (data.redirect) {
                    window.location.href = data.redirect;
                    return;
                }

                setLoading(submit, false);
            } catch (error) {
                setFeedback(form, 'error', 'Une erreur est survenue. Merci de réessayer.', {});
                setLoading(submit, false);
            }
        });
    });

    /* ── Composition Chart Tooltips ── */
    const initCompositionTooltips = (root = document) => {
        const charts = root.querySelectorAll('.composition-chart');
        charts.forEach((chart) => {
            if (chart.dataset.tooltipBound === '1') return;

            let tooltip = chart.querySelector('.composition-tooltip');
            if (!tooltip) {
                tooltip = document.createElement('span');
                tooltip.className = 'composition-tooltip';
                chart.appendChild(tooltip);
            }

            const segmentTargets = chart.querySelectorAll('[data-tooltip]');
            const chartTooltip = chart.getAttribute('data-tooltip');
            const targets = segmentTargets.length ? Array.from(segmentTargets) : (chartTooltip ? [chart] : []);

            if (!targets.length) return;

            const showTooltip = (target) => {
                const label = target.getAttribute('data-tooltip') || '';
                if (!label) return;
                tooltip.textContent = label;
                tooltip.style.opacity = '1';
            };

            const moveTooltip = (event) => {
                const rect = chart.getBoundingClientRect();
                tooltip.style.left = (event.clientX - rect.left) + 'px';
                tooltip.style.top = (event.clientY - rect.top - 10) + 'px';
            };

            const hideTooltip = () => {
                tooltip.style.opacity = '0';
            };

            targets.forEach((target) => {
                target.addEventListener('mouseenter', function (event) {
                    showTooltip(target);
                    moveTooltip(event);
                });
                target.addEventListener('mousemove', moveTooltip);
                target.addEventListener('mouseleave', hideTooltip);
            });

            chart.dataset.tooltipBound = '1';
        });
    };

    window.initCompositionTooltips = initCompositionTooltips;
    initCompositionTooltips();
})();
