document.addEventListener('DOMContentLoaded', function () {
    const questionForm = document.querySelector('[data-question-form]');
    const disciplineSelects = document.querySelectorAll('[data-discipline-select]');
    const questionModal = document.querySelector('[data-question-modal]');
    const openQuestionModalButtons = document.querySelectorAll('[data-open-question-modal]');
    const closeQuestionModalButtons = document.querySelectorAll('[data-close-question-modal]');
    const examBuilderForm = document.querySelector('[data-exam-builder-form]');
    const selectedCount = document.querySelector('[data-selected-count]');
    const selectedList = document.querySelector('[data-selected-list]');
    const menuToggle = document.querySelector('[data-menu-toggle]');
    const menuPanel = document.querySelector('[data-menu-panel]');
    const topbar = document.querySelector('.topbar');

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
            const stateLabel = item.parentElement?.querySelector('.exam-question-picker-check');

            if (stateLabel) {
                stateLabel.textContent = item.checked ? 'Selecionada' : 'Selecionar';
            }
        });

        selectedCount.textContent = checkedItems.length + ' questoes selecionadas';
        selectedList.innerHTML = '';

        if (checkedItems.length === 0) {
            selectedList.innerHTML = '<div class="workspace-quick-item" data-selected-empty><strong>Nenhuma questao selecionada</strong><p>Marque itens no banco ao lado para montar a prova.</p></div>';
            return;
        }

        checkedItems.forEach(function (item, index) {
            const title = item.dataset.questionTitle || ('Questao ' + (index + 1));
            const wrapper = document.createElement('div');
            const heading = document.createElement('strong');
            const text = document.createElement('p');

            wrapper.className = 'workspace-quick-item';
            heading.textContent = (index + 1) + '. ' + title;
            text.textContent = 'Item pronto para entrar na prova atual.';
            wrapper.appendChild(heading);
            wrapper.appendChild(text);
            selectedList.appendChild(wrapper);
        });
    }

    if (examBuilderForm) {
        examBuilderForm.addEventListener('change', function (event) {
            if (event.target.matches('[data-exam-question]')) {
                syncExamBuilderSelection();
            }
        });
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

    function addOptionRow() {
        const markup = template.innerHTML
            .replace(/__INDEX__/g, String(nextOptionIndex))
            .replace(/__LABEL__/g, optionLabel(optionsContainer.children.length));

        optionsContainer.insertAdjacentHTML('beforeend', markup);
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
    toggleQuestionSections();
    refreshOptionLabels();
});
