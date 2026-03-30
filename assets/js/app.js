document.addEventListener('DOMContentLoaded', function () {
    const body = document.body;
    const questionForm = document.querySelector('[data-question-form]');
    const disciplineSelects = document.querySelectorAll('[data-discipline-select]');
    const questionModal = document.querySelector('[data-question-modal]');
    const openQuestionModalButtons = document.querySelectorAll('[data-open-question-modal]');
    const closeQuestionModalButtons = document.querySelectorAll('[data-close-question-modal]');
    const examBuilderForm = document.querySelector('[data-exam-builder-form]');
    const selectedCount = document.querySelector('[data-selected-count]');
    const selectedList = document.querySelector('[data-selected-list]');
    const selectedDrawer = document.querySelector('[data-selected-drawer]');
    const selectedToggle = document.querySelector('[data-selected-toggle]');
    const bankList = document.querySelector('[data-bank-list]');
    const bankLoadMore = document.querySelector('[data-bank-load-more]');
    const menuToggle = document.querySelector('[data-menu-toggle]');
    const menuPanel = document.querySelector('[data-menu-panel]');
    const topbar = document.querySelector('.topbar');
    const examMetaForm = document.querySelector('[data-exam-meta-form]');
    const xeroxSwitcher = document.querySelector('[data-xerox-switcher]');
    const messageToast = document.querySelector('[data-message-toast]');
    const passwordToggleButtons = document.querySelectorAll('[data-password-toggle]');

    if (messageToast && body) {
        messageToast.addEventListener('click', function () {
            const messageId = messageToast.dataset.messageId || '';
            const csrfToken = body.dataset.csrfToken || '';
            const formData = new FormData();

            formData.append('_token', csrfToken);
            formData.append('message_id', messageId);

            messageToast.classList.add('is-dismissing');

            window.setTimeout(function () {
                messageToast.hidden = true;
            }, 260);

            window.fetch('message-dismiss.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            }).catch(function () {
                // ignore dismiss failures
            });
        });
    }

    passwordToggleButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const wrapper = button.closest('.password-input-wrap');
            const input = wrapper ? wrapper.querySelector('[data-password-input]') : null;
            const icon = button.querySelector('i');
            const text = button.querySelector('span');

            if (!input) {
                return;
            }

            const showPassword = input.type === 'password';
            input.type = showPassword ? 'text' : 'password';
            button.setAttribute('aria-pressed', showPassword ? 'true' : 'false');
            button.setAttribute('aria-label', showPassword ? 'Ocultar senha' : 'Mostrar senha');

            if (icon) {
                icon.className = showPassword ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
            }

            if (text) {
                text.textContent = showPassword ? 'Ocultar' : 'Mostrar';
            }
        });
    });

    if (menuToggle && menuPanel && topbar) {
        menuToggle.addEventListener('click', function () {
            const isOpen = topbar.classList.toggle('is-open');
            menuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        document.addEventListener('click', function (event) {
            if (!topbar.contains(event.target)) {
                topbar.classList.remove('is-open');
                menuToggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    const constructionWord = document.querySelector('[data-construction-word]');
    const constructionDots = document.querySelector('[data-construction-dots]');
    const constructionScene = document.querySelector('.home-minimal-scene');

    if (constructionWord && constructionDots) {
        const words = ['detalhes', 'ideias', 'blocos', 'provas', 'acabamentos'];
        let wordIndex = 0;
        let dotsCount = 3;

        window.setInterval(function () {
            wordIndex = (wordIndex + 1) % words.length;
            constructionWord.textContent = words[wordIndex];
            constructionWord.parentElement?.classList.remove('is-pulsing');
            void constructionWord.offsetWidth;
            constructionWord.parentElement?.classList.add('is-pulsing');
        }, 1800);

        window.setInterval(function () {
            dotsCount = dotsCount % 3 + 1;
            constructionDots.textContent = '.'.repeat(dotsCount);
        }, 420);
    }

    if (constructionScene) {
        window.setInterval(function () {
            constructionScene.classList.remove('is-bumping');
            void constructionScene.offsetWidth;
            constructionScene.classList.add('is-bumping');
        }, 3200);
    }

    if (examMetaForm) {
        const examCreateNavLinks = examMetaForm.ownerDocument.querySelectorAll('.exam-create-nav-link');
        const headerPreviewShell = examMetaForm.ownerDocument.querySelector('[data-header-preview-shell]');
        const headerPreviewTitle = examMetaForm.ownerDocument.querySelector('[data-header-preview-title]');
        const headerPreviewSubtitle = examMetaForm.ownerDocument.querySelector('[data-header-preview-subtitle]');
        const headerPreviewLeftLogo = examMetaForm.ownerDocument.querySelector('[data-header-preview-logo-left]');
        const headerPreviewRightLogo = examMetaForm.ownerDocument.querySelector('[data-header-preview-logo-right]');
        const headerPreviewCode = examMetaForm.ownerDocument.querySelector('[data-header-preview-code]');
        const headerPreviewDraftTitle = examMetaForm.ownerDocument.querySelector('[data-header-preview-draft-title]');
        const headerPreviewDateMeta = examMetaForm.ownerDocument.querySelector('[data-header-preview-date-meta]');
        const headerPreviewExamLabel = examMetaForm.ownerDocument.querySelector('[data-header-preview-exam-label]');
        const headerPreviewTeacher = examMetaForm.ownerDocument.querySelector('[data-header-preview-teacher]');
        const headerPreviewComponent = examMetaForm.ownerDocument.querySelector('[data-header-preview-component]');
        const headerPreviewClass = examMetaForm.ownerDocument.querySelector('[data-header-preview-class]');
        const headerEditor = examMetaForm.ownerDocument.querySelector('#identificacao-prova');
        const headerEditorTriggers = examMetaForm.ownerDocument.querySelectorAll('[data-open-header-editor]');
        const responseModeInput = examMetaForm.ownerDocument.querySelector('[data-response-mode-input]');
        const answerPreviewQuantityInput = examMetaForm.ownerDocument.querySelector('[data-answer-preview-quantity-input]');
        const answerPreviewOrientationInput = examMetaForm.ownerDocument.querySelector('[data-answer-preview-orientation-input]');
        const answerPreviewClass = examMetaForm.ownerDocument.querySelector('[data-answer-preview-class]');
        const answerPreviewDate = examMetaForm.ownerDocument.querySelector('[data-answer-preview-date]');
        const answerPreviewLabel = examMetaForm.ownerDocument.querySelector('[data-answer-preview-label]');
        const answerPreviewCountLabels = examMetaForm.ownerDocument.querySelectorAll('[data-answer-preview-count-label]');
        const answerPreviewModeName = examMetaForm.ownerDocument.querySelector('[data-answer-preview-mode-name]');
        const answerPreviewModes = examMetaForm.ownerDocument.querySelectorAll('[data-answer-preview-mode]');
        const answerPatternBadge = examMetaForm.ownerDocument.querySelector('[data-answer-pattern-badge]');
        const answerPatternCards = examMetaForm.ownerDocument.querySelectorAll('[data-answer-pattern-card]');
        const answerPatternCountLabels = examMetaForm.ownerDocument.querySelectorAll('[data-answer-pattern-count]');
        const responseModeButtons = examMetaForm.ownerDocument.querySelectorAll('[data-response-mode-option]');
        const bodyPreviewShell = examMetaForm.ownerDocument.querySelector('[data-body-preview-shell]');
        const bodyPreviewIntro = examMetaForm.ownerDocument.querySelector('[data-body-preview-intro]');
        const alertPreviewText = examMetaForm.ownerDocument.querySelector('[data-alert-preview-text]');
        const footerPreviewText = examMetaForm.ownerDocument.querySelector('[data-footer-preview-text]');
        const modelPreviewShell = examMetaForm.ownerDocument.querySelector('[data-model-preview-shell]');
        const modelPreviewStyleLabel = examMetaForm.ownerDocument.querySelector('[data-model-preview-style-label]');
        const modelPreviewStyleBadge = examMetaForm.ownerDocument.querySelector('[data-model-preview-style-badge]');
        const modelPreviewResponseLabel = examMetaForm.ownerDocument.querySelector('[data-model-preview-response-label]');
        let responseModeLabels = {};
        let styleLabels = {};

        try {
            responseModeLabels = JSON.parse(examMetaForm.dataset.responseModeLabels || '{}');
        } catch (error) {
            responseModeLabels = {};
        }

        try {
            styleLabels = JSON.parse(examMetaForm.dataset.styleLabels || '{}');
        } catch (error) {
            styleLabels = {};
        }

        function openDisclosureFromHash(hash) {
            if (!hash || hash.charAt(0) !== '#') {
                return;
            }

            const target = examMetaForm.ownerDocument.querySelector(hash);

            if (target && target.tagName === 'DETAILS') {
                target.open = true;
            }
        }

        function openHeaderEditor() {
            if (!headerEditor) {
                return;
            }

            headerEditor.open = true;
            headerEditor.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            const firstField = headerEditor.querySelector('input, select, textarea');

            if (firstField) {
                window.setTimeout(function () {
                    firstField.focus();
                }, 120);
            }
        }

        const summaryFields = examMetaForm.ownerDocument.querySelectorAll('[data-summary-field]');

        function formatDate(value) {
            if (!value || !/^\d{4}-\d{2}-\d{2}$/.test(value)) {
                return 'Não informada';
            }

            const parts = value.split('-');
            return parts[2] + '/' + parts[1] + '/' + parts[0];
        }

        function fieldValue(name) {
            const input = examMetaForm.querySelector('[name="' + name + '"]');
            return input ? input.value.trim() : '';
        }

        function normalizedCmValue(name, fallback) {
            const parsed = parseFloat((fieldValue(name) || '').replace(',', '.'));
            return Number.isFinite(parsed) && parsed > 0 ? parsed : fallback;
        }

        function cmToPx(value) {
            return Math.round(value * 37.7952755906);
        }

        function normalizedPreviewQuantity() {
            const parsed = parseInt(fieldValue('answer_preview_quantity') || '0', 10);
            return Number.isFinite(parsed) && parsed > 0 ? parsed : 1;
        }

        function previewQuantityLabel(quantity) {
            return quantity + ' questão(ões) na prévia';
        }

        function previewOrientationValue() {
            return fieldValue('answer_preview_orientation') === 'horizontal' ? 'horizontal' : 'vertical';
        }

        function syncAnswerPreviewPalette() {
            const previewWidth = fieldValue('answer_preview_width_mode') === 'full'
                ? '100%'
                : normalizedCmValue('answer_preview_size_cm', 18) + 'cm';
            const previewFontSize = Math.max(10, Math.min(24, parseInt(fieldValue('answer_preview_font_size') || '13', 10) || 13));

            examMetaForm.style.setProperty('--answer-preview-width', previewWidth);
            examMetaForm.style.setProperty('--answer-preview-height', normalizedCmValue('answer_preview_height_cm', 7) + 'cm');
            examMetaForm.style.setProperty('--answer-preview-font-size', previewFontSize + 'px');
            examMetaForm.style.setProperty('--answer-preview-card-bg', fieldValue('answer_preview_card_color') || '#f7f3ff');
            examMetaForm.style.setProperty('--answer-preview-surface', fieldValue('answer_preview_surface_color') || '#ffffff');
            examMetaForm.style.setProperty('--answer-preview-accent', fieldValue('answer_preview_accent_color') || '#5b34d6');
            examMetaForm.style.setProperty('--answer-preview-line', fieldValue('answer_preview_line_color') || '#cbd5e1');
        }

        function responseModeLabel(mode) {
            const selectedOption = examMetaForm.querySelector('[name="response_mode"] option:checked');

            if (selectedOption) {
                return (selectedOption.textContent || 'Sem resposta').trim();
            }

            return responseModeLabels[mode] || 'Sem resposta';
        }

        function styleModeLabel(style) {
            return styleLabels[style] || 'Questões em card em duas colunas';
        }

        function previewQuestionNumber(index) {
            return String(index).padStart(2, '0');
        }

        function renderAnswerPreviewRows(mode, quantity) {
            let rows = '';

            for (let index = 1; index <= quantity; index += 1) {
                const number = previewQuestionNumber(index);

                if (mode === 'lateral_answer_key') {
                    rows += '<div class="exam-builder-answer-pattern-row exam-builder-answer-pattern-row--inline">'
                        + '<span class="exam-builder-answer-number">' + number + '</span>'
                        + '<span class="exam-builder-answer-chip">A</span>'
                        + '<span class="exam-builder-answer-chip">B</span>'
                        + '<span class="exam-builder-answer-chip">C</span>'
                        + '<span class="exam-builder-answer-chip">D</span>'
                        + '</div>';
                    continue;
                }

                if (mode === 'bubble_answer_sheet') {
                    rows += '<div class="exam-builder-answer-pattern-row">'
                        + '<span class="exam-builder-answer-number">' + number + '</span>'
                        + '<span class="exam-builder-answer-bubble-option"><small>A</small><span class="exam-builder-answer-bubble"></span></span>'
                        + '<span class="exam-builder-answer-bubble-option"><small>B</small><span class="exam-builder-answer-bubble"></span></span>'
                        + '<span class="exam-builder-answer-bubble-option"><small>C</small><span class="exam-builder-answer-bubble"></span></span>'
                        + '<span class="exam-builder-answer-bubble-option"><small>D</small><span class="exam-builder-answer-bubble"></span></span>'
                        + '<span class="exam-builder-answer-bubble-option"><small>E</small><span class="exam-builder-answer-bubble"></span></span>'
                        + '</div>';
                    continue;
                }

                rows += '<div class="exam-builder-answer-pattern-row exam-builder-answer-pattern-row--sheet">'
                    + '<span class="exam-builder-answer-number">' + number + '</span>'
                    + '<span class="exam-builder-answer-line"></span>'
                    + '</div>';
            }

            return rows;
        }

        function renderAnswerPreviewLists(responseMode) {
            const quantity = normalizedPreviewQuantity();
            const orientation = previewOrientationValue();

            answerPreviewModes.forEach(function (preview) {
                const mode = preview.getAttribute('data-answer-preview-mode') || '';
                preview.innerHTML = renderAnswerPreviewRows(mode, quantity);
                preview.classList.toggle('is-scrollable', quantity > 12);
                preview.classList.toggle('is-horizontal', orientation === 'horizontal');
                preview.classList.toggle('is-vertical', orientation !== 'horizontal');
                preview.hidden = mode !== responseMode;
            });

            answerPreviewCountLabels.forEach(function (label) {
                label.textContent = previewQuantityLabel(quantity);
            });

            answerPatternCountLabels.forEach(function (label) {
                label.textContent = previewQuantityLabel(quantity);
            });
        }

        function resolvedSummaryValue(name) {
            const values = {
                draft_title: fieldValue('draft_title') || 'Não informado',
                exam_label: fieldValue('exam_label') || 'AVALIAÇÃO',
                school_name: fieldValue('school_name') || 'COLÉGIO / ESCOLA',
                teacher_name: fieldValue('teacher_name') || 'Professor não informado',
                component_name: fieldValue('component_name') || fieldValue('discipline') || 'Não informado',
                year_reference: fieldValue('year_reference') || 'Não informado',
                class_name: fieldValue('class_name') || 'Não informado',
                application_date: formatDate(fieldValue('application_date')),
                discipline: fieldValue('discipline') || 'Não informada',
                exam_style_label: styleModeLabel(fieldValue('exam_style') || 'double_column')
            };

            return values[name] || '';
        }

        function syncSummaryVisibility() {
            const toggles = examMetaForm.querySelectorAll('[data-summary-visibility-toggle]');

            toggles.forEach(function (toggle) {
                const target = toggle.getAttribute('data-summary-visibility-toggle') || '';
                const summaryItem = examMetaForm.ownerDocument.querySelector('[data-summary-item="' + target + '"]');

                if (!summaryItem) {
                    return;
                }

                summaryItem.hidden = !toggle.checked;
            });
        }

        function syncExamHeaderSummary() {
            summaryFields.forEach(function (field) {
                const key = field.dataset.summaryField || '';
                field.textContent = resolvedSummaryValue(key);
            });

            syncSummaryVisibility();
        }

        function syncHeaderPreview() {
            if (!headerPreviewShell || !headerPreviewTitle || !headerPreviewSubtitle) {
                return;
            }

            const schoolName = fieldValue('school_name') || 'COLÉGIO / ESCOLA';
            const schoolSubtitle = fieldValue('school_subtitle') || 'Ensino Fundamental, Médio e Profissionalizante';
            const bgColor = fieldValue('header_background_color') || '#ffffff';
            const titleColor = fieldValue('header_title_color') || '#334155';
            const subtitleColor = fieldValue('header_subtitle_color') || '#64748b';
            const titleSize = fieldValue('header_title_size') || '20';
            const subtitleSize = fieldValue('header_subtitle_size') || '16';
            const draftTitle = fieldValue('draft_title') || 'Prova sem título';
            const logoSizeCm = normalizedCmValue('header_logo_size', 2.2);
            const headerHeightCm = normalizedCmValue('header_min_height', 3.2);
            const leftLogo = fieldValue('header_logo_left');
            const rightLogo = fieldValue('header_logo_right');
            const examLabel = fieldValue('exam_label') || 'AVALIAÇÃO';
            const teacherName = fieldValue('teacher_name') || 'Professor';
            const componentName = fieldValue('component_name') || fieldValue('discipline') || 'Componente não informado';
            const classNameDisplay = fieldValue('class_name') || 'Não informada';
            const dateDisplay = fieldValue('application_date') ? formatDate(fieldValue('application_date')) : 'Definir data';
            const responseMode = fieldValue('response_mode') || 'separate_answer_sheet';
            const responseLabel = responseModeLabel(responseMode);
            const className = (fieldValue('class_name') || 'GER').replace(/[^A-Za-z0-9]/g, '').toUpperCase().slice(0, 4) || 'GER';
            const componentCode = componentName
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/[^A-Za-z0-9 ]/g, ' ')
                .trim()
                .split(/\s+/)
                .map(function (part) { return part.slice(0, 2).toUpperCase(); })
                .join('')
                .slice(0, 4) || 'COMP';
            const dateToken = (fieldValue('application_date') || new Date().toISOString().slice(0, 10)).replace(/-/g, '');
            const previewCode = 'PRV-' + dateToken + '-' + className + '-' + componentCode;

            headerPreviewShell.style.backgroundColor = bgColor;
            headerPreviewShell.style.minHeight = cmToPx(headerHeightCm) + 'px';
            headerPreviewShell.style.setProperty('--header-preview-logo-size', cmToPx(logoSizeCm) + 'px');
            headerPreviewTitle.textContent = schoolName;
            headerPreviewSubtitle.textContent = schoolSubtitle;
            headerPreviewTitle.style.color = titleColor;
            headerPreviewSubtitle.style.color = subtitleColor;
            headerPreviewTitle.style.fontSize = (parseInt(titleSize, 10) || 20) + 'px';
            headerPreviewSubtitle.style.fontSize = (parseInt(subtitleSize, 10) || 16) + 'px';
            syncAnswerPreviewPalette();

            if (headerPreviewLeftLogo) {
                headerPreviewLeftLogo.innerHTML = '';
                const leftImage = document.createElement('img');
                leftImage.src = leftLogo || 'https://cdn.worldvectorlogo.com/logos/colegio-estadual-c-vico-militar-tancredo-de-almeida-neves.svg';
                leftImage.alt = 'Logo esquerda';
                headerPreviewLeftLogo.appendChild(leftImage);
            }

            if (headerPreviewRightLogo) {
                headerPreviewRightLogo.innerHTML = '';

                if (rightLogo !== '') {
                    const rightImage = document.createElement('img');
                    rightImage.src = rightLogo;
                    rightImage.alt = 'Logo direita';
                    headerPreviewRightLogo.appendChild(rightImage);
                }
            }

            if (headerPreviewCode) {
                headerPreviewCode.textContent = previewCode;
            }

            if (headerPreviewDraftTitle) {
                headerPreviewDraftTitle.textContent = draftTitle;
            }

            if (headerPreviewDateMeta) {
                headerPreviewDateMeta.textContent = fieldValue('application_date') ? 'Aplicação em ' + formatDate(fieldValue('application_date')) : 'Data da aplicação em definição';
            }

            if (headerPreviewExamLabel) {
                headerPreviewExamLabel.textContent = examLabel;
            }

            if (headerPreviewTeacher) {
                headerPreviewTeacher.textContent = teacherName;
            }

            if (headerPreviewComponent) {
                headerPreviewComponent.textContent = componentName;
            }

            if (headerPreviewClass) {
                headerPreviewClass.textContent = classNameDisplay;
            }

            if (answerPreviewClass) {
                answerPreviewClass.textContent = classNameDisplay;
            }

            if (answerPreviewDate) {
                answerPreviewDate.textContent = dateDisplay;
            }

            if (answerPreviewLabel) {
                answerPreviewLabel.textContent = 'Escolhido: ' + responseLabel;
            }

            if (answerPreviewModeName) {
                answerPreviewModeName.textContent = responseLabel;
            }

            renderAnswerPreviewLists(responseMode);

            if (answerPatternBadge) {
                answerPatternBadge.textContent = 'Escolhido: ' + responseLabel;
            }

            answerPatternCards.forEach(function (card) {
                const isActive = card.getAttribute('data-answer-pattern-card') === responseMode;
                card.classList.toggle('is-active', isActive);
                card.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });
        }

        function previewColumnClass(styleValue) {
            if (styleValue === 'double_column') {
                return 'is-two-columns';
            }

            if (styleValue === 'triple_column') {
                return 'is-three-columns';
            }

            return 'is-one-column';
        }

        function syncBodyAndFooterPreview() {
            const styleValue = fieldValue('exam_style') || 'double_column';
            const styleLabel = styleModeLabel(styleValue);
            const responseLabel = responseModeLabel(fieldValue('response_mode') || 'separate_answer_sheet');
            const fontBase = parseInt(fieldValue('content_font_size') || '11', 10) || 11;
            const previewFont = fontBase;
            const introText = fieldValue('body_content') || 'Organize o tempo e resolva a prova com calma.';
            const alertText = fieldValue('header_content') || 'Use caneta azul ou preta e confira o cabeçalho antes de começar.';
            const footerText = fieldValue('footer_content') || 'Confira nome, turma e se todas as questões foram respondidas.';
            const styleClass = previewColumnClass(styleValue);

            if (bodyPreviewShell) {
                bodyPreviewShell.classList.remove('is-one-column', 'is-two-columns', 'is-three-columns');
                bodyPreviewShell.classList.add(styleClass);
                bodyPreviewShell.style.setProperty('--exam-preview-font-size', previewFont + 'px');
            }

            if (modelPreviewShell) {
                modelPreviewShell.style.setProperty('--exam-preview-font-size', previewFont + 'px');
                const modelBody = modelPreviewShell.querySelector('.exam-builder-model-body');

                if (modelBody) {
                    modelBody.classList.remove('is-one-column', 'is-two-columns', 'is-three-columns');
                    modelBody.classList.add(styleClass);
                }
            }

            if (bodyPreviewIntro) {
                bodyPreviewIntro.textContent = introText;
            }

            if (alertPreviewText) {
                alertPreviewText.textContent = alertText;
            }

            if (footerPreviewText) {
                footerPreviewText.textContent = footerText;
            }

            if (modelPreviewStyleLabel) {
                modelPreviewStyleLabel.textContent = styleLabel;
            }

            if (modelPreviewStyleBadge) {
                modelPreviewStyleBadge.textContent = styleLabel;
            }

            if (modelPreviewResponseLabel) {
                modelPreviewResponseLabel.textContent = responseLabel;
            }
        }

        examMetaForm.addEventListener('input', syncExamHeaderSummary);
        examMetaForm.addEventListener('change', syncExamHeaderSummary);
        syncExamHeaderSummary();
        examMetaForm.addEventListener('input', syncHeaderPreview);
        examMetaForm.addEventListener('change', syncHeaderPreview);
        syncHeaderPreview();
        examMetaForm.addEventListener('input', syncBodyAndFooterPreview);
        examMetaForm.addEventListener('change', syncBodyAndFooterPreview);
        syncBodyAndFooterPreview();

        function activateResponseMode(nextMode) {
            if (!responseModeInput || nextMode === '') {
                return;
            }

            responseModeInput.value = nextMode;
            syncHeaderPreview();
            syncBodyAndFooterPreview();
        }

        responseModeButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                activateResponseMode(button.getAttribute('data-response-mode-option') || '');
            });

            button.addEventListener('keydown', function (event) {
                if (event.key !== 'Enter' && event.key !== ' ') {
                    return;
                }

                event.preventDefault();
                activateResponseMode(button.getAttribute('data-response-mode-option') || '');
            });
        });

        headerEditorTriggers.forEach(function (button) {
            button.addEventListener('click', function (event) {
                event.preventDefault();
                openHeaderEditor();
            });
        });

        examCreateNavLinks.forEach(function (link) {
            link.addEventListener('click', function () {
                openDisclosureFromHash(link.getAttribute('href') || '');
            });
        });

        openDisclosureFromHash(window.location.hash);
    }

    if (xeroxSwitcher) {
        const switchButtons = Array.from(xeroxSwitcher.querySelectorAll('[data-xerox-switch]'));
        const panels = Array.from(document.querySelectorAll('[data-xerox-panel]'));
        const defaultPanel = xeroxSwitcher.dataset.defaultPanel || (switchButtons[0]?.dataset.xeroxSwitch ?? '');

        function activateXeroxPanel(panelName) {
            switchButtons.forEach(function (button) {
                const isActive = button.dataset.xeroxSwitch === panelName;
                button.classList.toggle('is-active', isActive);
                button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });

            panels.forEach(function (panel) {
                panel.toggleAttribute('hidden', panel.dataset.xeroxPanel !== panelName);
            });
        }

        switchButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                activateXeroxPanel(button.dataset.xeroxSwitch || '');
            });
        });

        if (defaultPanel !== '') {
            activateXeroxPanel(defaultPanel);
        }
    }

    function setQuestionModalState(isOpen) {
        if (!questionModal) {
            return;
        }

        questionModal.classList.toggle('is-open', isOpen);
        document.body.classList.toggle('has-modal-open', isOpen);
    }

    openQuestionModalButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            setQuestionModalState(true);
        });
    });

    closeQuestionModalButtons.forEach(function (button) {
        button.addEventListener('click', function (event) {
            if (button.tagName === 'A') {
                return;
            }

            event.preventDefault();
            setQuestionModalState(false);
        });
    });

    if (questionModal && questionModal.classList.contains('is-open')) {
        document.body.classList.add('has-modal-open');
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && questionModal && questionModal.classList.contains('is-open')) {
            const editingMode = !!questionForm?.querySelector('[name="question_id"]');

            if (!editingMode) {
                setQuestionModalState(false);
            }
        }
    });

    function syncExamBuilderSelection() {
        if (!examBuilderForm || !selectedCount || !selectedList) {
            return;
        }

        const allItems = Array.from(examBuilderForm.querySelectorAll('[data-exam-question]'));
        const checkedItems = allItems.filter(function (item) {
            return item.checked;
        });

        allItems.forEach(function (item) {
            const stateLabel = item.parentElement?.querySelector('.exam-question-picker-check, .simple-question-picker-state');

            if (stateLabel) {
                stateLabel.textContent = item.checked ? 'Selecionada' : 'Selecionar';
            }
        });

        const countText = String(checkedItems.length);
        examBuilderForm.querySelectorAll('[data-selected-count]').forEach(function (node) {
            node.textContent = countText;
        });
        selectedList.innerHTML = '';

        if (checkedItems.length === 0) {
            if (selectedDrawer) {
                selectedDrawer.hidden = true;
                selectedDrawer.classList.remove('is-open');
            }
            if (selectedToggle) {
                selectedToggle.setAttribute('aria-expanded', 'false');
            }
            selectedList.innerHTML = '<div class="empty-state" data-selected-empty><h2>Nenhuma questão selecionada</h2><p>Marque as questões abaixo para montar a prova.</p></div>';
            return;
        }

        if (selectedDrawer) {
            selectedDrawer.hidden = false;
            selectedDrawer.classList.add('is-open');
        }
        if (selectedToggle) {
            selectedToggle.setAttribute('aria-expanded', 'true');
        }

        checkedItems.forEach(function (item, index) {
            const title = item.dataset.questionTitle || ('Questao ' + (index + 1));
            const wrapper = document.createElement('article');
            const heading = document.createElement('strong');

            wrapper.className = 'simple-list-item';
            wrapper.setAttribute('data-selected-item', '');
            heading.textContent = title;
            const badge = document.createElement('div');
            const copy = document.createElement('div');

            badge.className = 'exam-selected-item-index';
            badge.textContent = String(index + 1);
            copy.className = 'exam-selected-item-copy';
            copy.appendChild(heading);
            wrapper.appendChild(badge);
            wrapper.appendChild(copy);
            selectedList.appendChild(wrapper);
        });
    }

    if (examBuilderForm) {
        examBuilderForm.addEventListener('change', function (event) {
            if (event.target.matches('[data-exam-question]')) {
                syncExamBuilderSelection();
            }
        });

        if (selectedToggle && selectedDrawer) {
            selectedToggle.addEventListener('click', function () {
                const shouldOpen = selectedDrawer.hidden || !selectedDrawer.classList.contains('is-open');
                selectedDrawer.hidden = !shouldOpen;
                selectedDrawer.classList.toggle('is-open', shouldOpen);
                selectedToggle.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
            });
        }

        if (bankLoadMore && bankList) {
            bankLoadMore.addEventListener('click', function () {
                const currentVisible = parseInt(bankList.dataset.bankVisibleCount || '10', 10);
                const nextVisible = currentVisible + 10;
                const items = Array.from(bankList.querySelectorAll('[data-bank-item]'));

                items.forEach(function (item, index) {
                    item.classList.toggle('is-hidden-bank-item', index >= nextVisible);
                });

                bankList.dataset.bankVisibleCount = String(nextVisible);
                const remaining = Math.max(items.length - nextVisible, 0);
                bankLoadMore.hidden = remaining <= 0;
                bankLoadMore.textContent = 'Ver mais ' + Math.min(10, remaining) + ' questões';
            });

            const bankItems = Array.from(bankList.querySelectorAll('[data-bank-item]'));
            const initialRemaining = Math.max(bankItems.length - 10, 0);
            bankLoadMore.hidden = initialRemaining <= 0;
            bankLoadMore.textContent = 'Ver mais ' + Math.min(10, initialRemaining) + ' questões';
        }

        syncExamBuilderSelection();
    }

    function optionLabel(index) {
        let label = '';
        let number = index + 1;

        while (number > 0) {
            const remainder = (number - 1) % 26;
            label = String.fromCharCode(65 + remainder) + label;
            number = Math.floor((number - 1) / 26);
        }

        return label;
    }

    function normalizeEditorText(text, singleLine = false) {
        let normalized = String(text || '')
            .replace(/\r\n?/g, '\n')
            .replace(/\u00a0|\u2007|\u202f/g, ' ')
            .replace(/[\u200b-\u200d\ufeff]/g, '')
            .replace(/[ \t]+\n/g, '\n')
            .replace(/\n{3,}/g, '\n\n');

        normalized = normalized.replace(
            /(^|[^\d])(\d)\s*(\d)\s*\\(?:d)?frac\s*\{\s*\2\s*\}\s*\{\s*\3\s*\}\s*\3\s*\2(?!\d)/g,
            function (match, prefix, numerator, denominator) {
                return prefix + '\\frac{' + numerator + '}{' + denominator + '}';
            }
        ).trim();

        if (singleLine) {
            normalized = normalized.replace(/\s+/g, ' ').trim();
        }

        return normalized;
    }

    function convertScriptText(text, mode) {
        const maps = {
            sup: {
                '0': '⁰', '1': '¹', '2': '²', '3': '³', '4': '⁴', '5': '⁵', '6': '⁶', '7': '⁷', '8': '⁸', '9': '⁹',
                '+': '⁺', '-': '⁻', '=': '⁼', '(': '⁽', ')': '⁾', 'n': 'ⁿ', 'i': 'ⁱ'
            },
            sub: {
                '0': '₀', '1': '₁', '2': '₂', '3': '₃', '4': '₄', '5': '₅', '6': '₆', '7': '₇', '8': '₈', '9': '₉',
                '+': '₊', '-': '₋', '=': '₌', '(': '₍', ')': '₎', 'a': 'ₐ', 'e': 'ₑ', 'h': 'ₕ', 'i': 'ᵢ', 'j': 'ⱼ',
                'k': 'ₖ', 'l': 'ₗ', 'm': 'ₘ', 'n': 'ₙ', 'o': 'ₒ', 'p': 'ₚ', 'r': 'ᵣ', 's': 'ₛ', 't': 'ₜ', 'u': 'ᵤ',
                'v': 'ᵥ', 'x': 'ₓ'
            }
        };
        const map = maps[mode] || {};
        let converted = '';

        for (const char of String(text || '')) {
            if (!Object.prototype.hasOwnProperty.call(map, char)) {
                return mode === 'sup' ? '^(' + text + ')' : '_(' + text + ')';
            }

            converted += map[char];
        }

        return converted;
    }

    function scriptModeFromElement(node) {
        if (!(node instanceof HTMLElement)) {
            return '';
        }

        const verticalAlign = (node.style?.verticalAlign || '').toLowerCase();
        const className = (node.className || '').toString().toLowerCase();

        if (verticalAlign === 'super' || className.includes('superscript')) {
            return 'sup';
        }

        if (verticalAlign === 'sub' || className.includes('subscript')) {
            return 'sub';
        }

        return '';
    }

    function listItemPrefix(node) {
        const parent = node.parentElement;

        if (!parent || parent.tagName.toLowerCase() !== 'ol') {
            return '- ';
        }

        const items = Array.from(parent.children).filter(function (child) {
            return child.tagName && child.tagName.toLowerCase() === 'li';
        });
        const index = items.indexOf(node);
        const start = Number.parseInt(parent.getAttribute('start') || '1', 10);
        const resolvedStart = Number.isNaN(start) ? 1 : start;

        return String(resolvedStart + Math.max(index, 0)) + '. ';
    }

    function hiddenRichNode(node) {
        if (!(node instanceof HTMLElement)) {
            return false;
        }

        const className = (node.className || '').toString().toLowerCase();

        return node.hidden
            || node.getAttribute('aria-hidden') === 'true'
            || className.includes('katex-html')
            || className.includes('mjx-assistive-mml')
            || className.includes('mathjax-preview')
            || className.includes('sr-only')
            || className.includes('screen-reader')
            || className.includes('visually-hidden');
    }

    function extractMathText(node) {
        if (!(node instanceof Element)) {
            return '';
        }

        const tag = node.tagName.toLowerCase();
        const className = (node.className || '').toString().toLowerCase();
        const annotation = node.matches('annotation')
            ? node
            : node.querySelector('annotation[encoding*="tex"]');

        if (annotation && annotation.textContent) {
            return normalizeEditorText(annotation.textContent, true);
        }

        if (tag === 'math') {
            return normalizeEditorText(node.textContent || '', true);
        }

        if (className.includes('katex') || className.includes('mathjax') || tag === 'mjx-container') {
            const ariaLabel = node.getAttribute('aria-label') || '';

            if (ariaLabel.trim() !== '') {
                return normalizeEditorText(ariaLabel, true);
            }

            const assistiveMath = node.querySelector('math');

            if (assistiveMath && assistiveMath.textContent) {
                return normalizeEditorText(assistiveMath.textContent, true);
            }
        }

        return '';
    }

    function serializeRichNode(node) {
        if (node.nodeType === Node.TEXT_NODE) {
            return node.textContent || '';
        }

        if (node.nodeType !== Node.ELEMENT_NODE) {
            return '';
        }

        const tag = node.tagName.toLowerCase();
        const className = (node.className || '').toString().toLowerCase();

        if (hiddenRichNode(node)) {
            return '';
        }

        const extractedMath = extractMathText(node);

        if (extractedMath !== '') {
            if (className.includes('katex-display') || className.includes('math-display')) {
                return '\n' + extractedMath + '\n';
            }

            return extractedMath;
        }

        if (tag === 'br') {
            return '\n';
        }

        if (tag === 'sup') {
            return convertScriptText(serializeRichChildren(node), 'sup');
        }

        if (tag === 'sub') {
            return convertScriptText(serializeRichChildren(node), 'sub');
        }

        const inferredScriptMode = scriptModeFromElement(node);

        if (inferredScriptMode !== '') {
            return convertScriptText(serializeRichChildren(node), inferredScriptMode);
        }

        if (tag === 'li') {
            return listItemPrefix(node) + normalizeEditorText(serializeRichChildren(node)) + '\n';
        }

        if (tag === 'ul' || tag === 'ol') {
            return '\n' + Array.from(node.childNodes).map(serializeRichNode).join('') + '\n';
        }

        if (tag === 'tr') {
            const cells = Array.from(node.children).map(function (cell) {
                return normalizeEditorText(serializeRichChildren(cell));
            }).filter(Boolean);

            return cells.join(' | ') + '\n';
        }

        if (tag === 'table') {
            return '\n' + Array.from(node.childNodes).map(serializeRichNode).join('') + '\n';
        }

        if (tag === 'pre' || tag === 'code') {
            return '\n' + (node.textContent || '') + '\n';
        }

        const blockTags = ['p', 'div', 'section', 'article', 'header', 'footer', 'aside', 'blockquote', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
        const content = serializeRichChildren(node);

        if (blockTags.includes(tag)) {
            return '\n' + content + '\n';
        }

        return content;
    }

    function serializeRichChildren(node) {
        return Array.from(node.childNodes).map(serializeRichNode).join('');
    }

    function htmlToStructuredText(html) {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        return normalizeEditorText(serializeRichChildren(doc.body));
    }

    function escapeHtml(text) {
        return String(text || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function renderMathFragmentsHtml(text) {
        return escapeHtml(text).replace(
            /\\(?:d)?frac\s*\{\s*([^{}]+?)\s*\}\s*\{\s*([^{}]+?)\s*\}/g,
            function (_, numerator, denominator) {
                return '<span class="math-fraction" aria-label="' + escapeHtml(numerator.trim()) + ' sobre ' + escapeHtml(denominator.trim()) + '">'
                    + '<span class="math-fraction-top">' + escapeHtml(numerator.trim()) + '</span>'
                    + '<span class="math-fraction-bottom">' + escapeHtml(denominator.trim()) + '</span>'
                    + '</span>';
            }
        );
    }

    function isMathExpressionLine(line) {
        const value = String(line || '').trim();

        if (!value) {
            return false;
        }

        if (!/(=|\\(?:d)?frac|\^|[+\-*/]=?|\b[a-z]\([^)]*\))/i.test(value) && !/^[a-z]$/i.test(value)) {
            return false;
        }

        if (/[!?;]/.test(value)) {
            return false;
        }

        const words = value.match(/[A-Za-zÀ-ÿ]+/g) || [];
        return words.length <= 6;
    }

    function renderFormattedPreviewHtml(text) {
        const normalized = normalizeEditorText(text);

        if (!normalized) {
            return 'A previa do enunciado aparece aqui.';
        }

        return normalized.split('\n').map(function (line) {
            const rendered = renderMathFragmentsHtml(line);
            return isMathExpressionLine(line)
                ? '<span class="math-expression-block">' + rendered + '</span>'
                : rendered;
        }).join('<br>');
    }

    function insertTextAtCursor(field, text) {
        const start = field.selectionStart ?? field.value.length;
        const end = field.selectionEnd ?? field.value.length;
        const before = field.value.slice(0, start);
        const after = field.value.slice(end);
        field.value = before + text + after;
        const caret = start + text.length;
        field.setSelectionRange(caret, caret);
        field.dispatchEvent(new Event('input', { bubbles: true }));
    }

    function autoResizeTextarea(field) {
        if (!(field instanceof HTMLTextAreaElement)) {
            return;
        }

        field.style.height = 'auto';
        field.style.height = Math.min(field.scrollHeight, 520) + 'px';
    }

    function bindRichPasteField(field) {
        if (!field || field.dataset.richPasteBound === '1') {
            return;
        }

        field.dataset.richPasteBound = '1';

        if (field instanceof HTMLTextAreaElement) {
            autoResizeTextarea(field);
            field.addEventListener('input', function () {
                autoResizeTextarea(field);
            });
        }

        field.addEventListener('paste', function (event) {
            const clipboard = event.clipboardData;

            if (!clipboard) {
                return;
            }

            const html = clipboard.getData('text/html');
            const plainText = clipboard.getData('text/plain');
            const normalized = html ? htmlToStructuredText(html) : normalizeEditorText(plainText);

            if (!normalized) {
                return;
            }

            event.preventDefault();
            insertTextAtCursor(field, normalized);
        });
    }

    function syncSubjectSelect(sourceSelect) {
        const targetId = sourceSelect.dataset.target;
        const targetSelect = targetId ? document.getElementById(targetId) : sourceSelect.parentElement?.parentElement?.querySelector('[data-subject-select]');

        if (!targetSelect) {
            return;
        }

        const disciplineId = sourceSelect.value;

        Array.from(targetSelect.options).forEach(function (option, index) {
            if (index === 0) {
                option.hidden = false;
                return;
            }

            const matches = !disciplineId || option.dataset.disciplineId === disciplineId;
            option.hidden = !matches;

            if (!matches && option.selected) {
                targetSelect.value = targetSelect.options[0]?.value || '';
            }
        });
    }

    disciplineSelects.forEach(function (select) {
        select.addEventListener('change', function () {
            syncSubjectSelect(select);
        });
        syncSubjectSelect(select);
    });

    if (!questionForm) {
        return;
    }

    const typeField = questionForm.querySelector('[name="question_type"]');
    const multipleChoiceSection = questionForm.querySelector('[data-question-section="multiple_choice"]');
    const discursiveSection = questionForm.querySelector('[data-question-section="discursive"]');
    const drawingSection = questionForm.querySelector('[data-question-section="drawing"]');
    const trueFalseSection = questionForm.querySelector('[data-question-section="true_false"]');
    const drawingSizeSelect = questionForm.querySelector('[data-drawing-size-select]');
    const drawingCustomField = questionForm.querySelector('[data-drawing-custom-field]');
    const optionsContainer = questionForm.querySelector('[data-options-container]');
    const addOptionButton = questionForm.querySelector('[data-add-option]');
    const promptField = questionForm.querySelector('[data-question-preview-source]');
    const titleField = questionForm.querySelector('[data-question-preview-title-source]');
    const previewOutput = questionForm.querySelector('[data-question-preview-output]');
    const previewTitle = questionForm.querySelector('[data-question-preview-title]');
    const previewType = questionForm.querySelector('[data-question-preview-type]');
    const previewVisibility = questionForm.querySelector('[data-question-preview-visibility]');
    const previewDiscipline = questionForm.querySelector('[data-question-preview-discipline]');
    const previewSubject = questionForm.querySelector('[data-question-preview-subject]');
    const visibilityField = questionForm.querySelector('[data-question-summary-visibility]');
    const disciplineField = questionForm.querySelector('[data-question-summary-discipline]');
    const subjectField = questionForm.querySelector('[data-question-summary-subject]');
    const template = document.getElementById('question-option-template');
    let nextOptionIndex = Number.parseInt(questionForm.dataset.nextOptionIndex || '0', 10);

    function toggleQuestionSections() {
        const type = typeField.value;

        multipleChoiceSection.classList.toggle('hidden', type !== 'multiple_choice');
        discursiveSection.classList.toggle('hidden', type !== 'discursive');
        drawingSection.classList.toggle('hidden', type !== 'drawing');
        trueFalseSection.classList.toggle('hidden', type !== 'true_false');
        toggleDrawingCustomField();
    }

    function toggleDrawingCustomField() {
        if (!drawingSizeSelect || !drawingCustomField) {
            return;
        }

        const showCustomField = typeField.value === 'drawing' && drawingSizeSelect.value === 'custom';
        drawingCustomField.classList.toggle('hidden', !showCustomField);
    }

    function refreshOptionLabels() {
        Array.from(optionsContainer.querySelectorAll('.option-editor-row')).forEach(function (row, index) {
            const label = row.querySelector('strong');

            if (label) {
                label.textContent = optionLabel(index);
            }
        });
    }

    function updateQuestionPreview() {
        if (promptField && previewOutput) {
            previewOutput.innerHTML = renderFormattedPreviewHtml(promptField.value);
        }

        if (titleField && previewTitle) {
            const normalizedTitle = normalizeEditorText(titleField.value, true);
            previewTitle.textContent = normalizedTitle || 'Titulo da questao';
        }

        if (typeField && previewType) {
            previewType.textContent = typeField.options[typeField.selectedIndex]?.text || 'Sem tipo';
        }

        if (visibilityField && previewVisibility) {
            previewVisibility.textContent = visibilityField.options[visibilityField.selectedIndex]?.text || 'Sem visibilidade';
        }

        if (disciplineField && previewDiscipline) {
            previewDiscipline.textContent = disciplineField.options[disciplineField.selectedIndex]?.text || 'Sem disciplina';
        }

        if (subjectField && previewSubject) {
            previewSubject.textContent = subjectField.options[subjectField.selectedIndex]?.text || 'Sem assunto';
        }
    }

    function bindPreviewSync(field) {
        if (!field) {
            return;
        }

        field.addEventListener('input', updateQuestionPreview);
        field.addEventListener('change', updateQuestionPreview);
    }

    function addOptionRow() {
        const markup = template.innerHTML
            .replace(/__INDEX__/g, String(nextOptionIndex))
            .replace(/__LABEL__/g, optionLabel(optionsContainer.children.length));

        optionsContainer.insertAdjacentHTML('beforeend', markup);
        const lastField = optionsContainer.lastElementChild?.querySelector('[data-rich-paste]');

        if (lastField) {
            bindRichPasteField(lastField);
        }

        nextOptionIndex += 1;
        refreshOptionLabels();
    }

    addOptionButton.addEventListener('click', addOptionRow);

    optionsContainer.addEventListener('click', function (event) {
        const removeButton = event.target.closest('[data-remove-option]');

        if (!removeButton) {
            return;
        }

        const row = removeButton.closest('.option-editor-row');

        if (!row) {
            return;
        }

        if (optionsContainer.children.length <= 2) {
            return;
        }

        row.remove();
        refreshOptionLabels();
    });

    typeField.addEventListener('change', toggleQuestionSections);
    if (drawingSizeSelect) {
        drawingSizeSelect.addEventListener('change', toggleDrawingCustomField);
    }
    questionForm.querySelectorAll('[data-rich-paste]').forEach(function (field) {
        bindRichPasteField(field);
    });
    bindPreviewSync(promptField);
    bindPreviewSync(titleField);
    bindPreviewSync(typeField);
    bindPreviewSync(visibilityField);
    bindPreviewSync(disciplineField);
    bindPreviewSync(subjectField);
    updateQuestionPreview();
    toggleQuestionSections();
    refreshOptionLabels();
});
