document.addEventListener('DOMContentLoaded', function () {
    const questionForm = document.querySelector('[data-question-form]');
    const disciplineSelects = document.querySelectorAll('[data-discipline-select]');

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
    const optionsContainer = questionForm.querySelector('[data-options-container]');
    const addOptionButton = questionForm.querySelector('[data-add-option]');
    const template = document.getElementById('question-option-template');
    let nextOptionIndex = Number.parseInt(questionForm.dataset.nextOptionIndex || '0', 10);

    function toggleQuestionSections() {
        const type = typeField.value;

        multipleChoiceSection.classList.toggle('hidden', type !== 'multiple_choice');
        discursiveSection.classList.toggle('hidden', type !== 'discursive');
        drawingSection.classList.toggle('hidden', type !== 'drawing');
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
    toggleQuestionSections();
    refreshOptionLabels();
});
