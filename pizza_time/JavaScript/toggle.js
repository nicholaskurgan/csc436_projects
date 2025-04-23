function toggleSubmit() {
    const checkbox = document.getElementById("termsCheckbox");
    document.getElementById("submitBtn").disabled = !checkbox.checked;
}
