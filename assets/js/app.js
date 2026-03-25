document.addEventListener('DOMContentLoaded', function () {
    const questionForm = document.querySelector('[data-question-form]');

    if (!questionForm) {
        return;
    }

    const typeField = questionForm.querySelector('[name="question_type"]');
    const multipleChoiceSection = questionForm.querySelector('[data-question-section="multiple_choice"]');
    const discursiveSection = questionForm.querySelector('[data-question-section="discursive"]');
    const trueFalseSection = questionForm.querySelector('[data-question-section="true_false"]');

    function toggleQuestionSections() {
        const type = typeField.value;

        multipleChoiceSection.classList.toggle('hidden', type !== 'multiple_choice');
        discursiveSection.classList.toggle('hidden', type !== 'discursive');
        trueFalseSection.classList.toggle('hidden', type !== 'true_false');
    }

    typeField.addEventListener('change', toggleQuestionSections);
    toggleQuestionSections();
});
