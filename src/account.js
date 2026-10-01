
//  View , Edit
function toggleEditMode() {
    const viewMode = document.getElementById('view-mode');
    const editMode = document.getElementById('edit-mode');

    if (viewMode.style.display === 'none') {

        viewMode.style.display = 'flex';
        editMode.style.display = 'none';
    } else {

        viewMode.style.display = 'none';
        editMode.style.display = 'flex';
    }
}


// ฟังก์ชันสำหรับสั่งให้ Input File ทำงาน
function triggerUpload() {
    document.getElementById('profile_upload').click();
}

function previewFile() {
    const previewBox = document.getElementById('preview-box');
    const file = document.querySelector('#profile_upload').files[0];
    const reader = new FileReader();

    reader.addEventListener("load", function () {
        previewBox.innerHTML = `<img id="img-preview" src="${reader.result}" style="width:100%; height:100%; object-fit:cover;">`;
    }, false);

    if (file) {
        reader.readAsDataURL(file);
    }
}