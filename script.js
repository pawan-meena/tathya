// script.js
document.getElementById("year").textContent = new Date().getFullYear();

document.addEventListener("DOMContentLoaded", function () {
  const form = document.querySelector("form");
  const responseMessage = document.getElementById("responseMessage");

  form.addEventListener("submit", function (event) {
    event.preventDefault();
    const email = document.getElementById("email").value;
    const formData = new FormData();
    formData.append("email", email);
    formData.append("message", "hello");

    fetch("https://academia.vioniko.net/umaapi/umaapi.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        console.log(data);
        if (data.status === "error" && data.message === "Email already exists") {
          responseMessage.textContent = "Email already exists";
          responseMessage.style.color = "red";
        } else {
          responseMessage.textContent = "Email sent successfully!";
          responseMessage.style.color = "green";
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        responseMessage.textContent = "Failed to send email";
        responseMessage.style.color = "red";
      });
  });
});
