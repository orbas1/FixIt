// Plus Minus servicemen Js 
const plusMinus = document.querySelectorAll('.plus-minus ');
plusMinus.forEach((element) => {
    const addButton = element.querySelector('.iconsax.add');
    const subButton = element.querySelector('.iconsax.sub');

    addButton ?.addEventListener('click', function () {
        const inputEl = this.parentNode.querySelector("input[type='number']");
        if (inputEl.value < 10) {
            inputEl.value = Number(inputEl.value) + 1;
        }
    });
    subButton ?.addEventListener('click', function () {
        const inputEl = this.parentNode.querySelector("input[type='number']");
        if (inputEl.value >= 2) {
            inputEl.value = Number(inputEl.value) - 1;
        }
    });
});