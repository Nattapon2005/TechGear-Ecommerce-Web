const dropdown = document.getElementById('sortDropdown');
const trigger = document.getElementById('sortTrigger');
const label = document.getElementById('sortLabel');
const optBoxes = document.querySelectorAll('.options-box');

const productItemImg = document.getElementById('productItemImg');

// เปิด/ปิด dropdown
trigger.addEventListener('click', () => dropdown.classList.toggle('open'));

// เลือก option
optBoxes.forEach(opt => {
    opt.addEventListener('click', () => {
        optBoxes.forEach(o => o.classList.remove('active'));
        opt.classList.add('active');

        // ใช้ cloneNode แล้วลบ icon ออกก่อนอ่านข้อความ
        const clone = opt.cloneNode(true);
        const icon = clone.querySelector('i');
        if (icon) icon.remove();
        label.textContent = clone.textContent.trim();

        dropdown.classList.remove('open');
    });
});



function render() {
    const grid = document.getElementById("productsGrid");

    grid.className = "product-item-con-grid " + (viewMode === "grid" ? "grid-view" : "list-view");



    const allProductImgs = document.querySelectorAll(".product-img-g");

    allProductImgs.forEach(img => {
        img.classList.toggle("img-grid", styleImg === "grid");
        img.classList.toggle("img-list", styleImg === "list");
    });
}


// คลิกนอก → ปิด
document.addEventListener('click', e => {
    if (!dropdown.contains(e.target)) dropdown.classList.remove('open');
});

// View toggle
const gridBtn = document.getElementById('gridBtn');
const listBtn = document.getElementById('listBtn');

gridBtn.addEventListener('click', () => {
    viewMode = "grid";
    styleImg = "grid";

    gridBtn.classList.add('active');
    listBtn.classList.remove('active');

    render();
});

listBtn.addEventListener('click', () => {
    viewMode = "list";
    styleImg = "list";

    listBtn.classList.add('active');
    gridBtn.classList.remove('active');

    render();
});



