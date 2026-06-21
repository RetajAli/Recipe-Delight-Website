
// FILTER RECIPES BY SEARCH
function filterRecipes() {
    const input = document.getElementById('recipeSearch').value.toLowerCase();
    const items = document.querySelectorAll('.menu-section li');
    items.forEach(item => {
        item.style.display = item.textContent.toLowerCase().includes(input) ? '' : 'none';
    });
}

// TOGGLE SECTIONS
document.addEventListener('DOMContentLoaded', () => {
    const headings = document.querySelectorAll('.menu-section h2');
    headings.forEach(heading => {
        heading.style.cursor = 'pointer';
        heading.addEventListener('click', () => {
            const list = heading.nextElementSibling;
            list.style.display = list.style.display === 'none' ? 'block' : 'none';
        });
    });
});

// HIGHLIGHT FAVORITE BUTTON
document.addEventListener('DOMContentLoaded', () => {
    const buttons = document.querySelectorAll('.menu-section button');
    buttons.forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault(); // Prevent actual form submission
            btn.classList.toggle('highlighted');
        });
    });
});
