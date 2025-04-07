function toggleMenu() {
    const menu = document.getElementById("mobile-menu");
    const isOpen = !menu.classList.contains("max-h-0");
  
    if (isOpen) {
      menu.classList.remove("max-h-screen");
      menu.classList.add("max-h-0");
    } else {
      menu.classList.remove("max-h-0");
      menu.classList.add("max-h-screen");
    }
  
    // Optional: close on link click
    document.querySelectorAll("#mobile-menu a").forEach(link => {
      link.addEventListener("click", () => {
        menu.classList.remove("max-h-screen");
        menu.classList.add("max-h-0");
      });
    });
  }


$(document).ready(function () {
    $("#contactForm").submit(function (e) {
        e.preventDefault();

        // Disable the submit button and change text
        $("#submitButton").prop("disabled", true).text("Sending...");

        // Get form values
        var name = $("#name").val().trim();
        var email = $("#email").val().trim();
        var phone = $("#phone").val().trim();
        var company = $("#company").val().trim();
        var message = $("#message").val().trim();

        // Validate inputs
        if (!validateForm(name, email, phone, message, company)) {
            $("#submitButton").prop("disabled", false).text("Send Message");
            return;
        }

        // AJAX request
        $.ajax({
            type: "POST",
            url: "process.php", // Update if needed
            data: { name: name, email: email, phone: phone, message: message, company:company },
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    showResponseMessage("Your message has been sent successfully! Please check your email.", "success");
                    $("#contactForm")[0].reset(); // Reset form
                } else {
                    showResponseMessage(response.message || "Something went wrong. Try again later.", "error");
                }
            },
            error: function () {
                showResponseMessage("Server error. Please try again later.", "error");
            },
            complete: function () {
                $("#submitButton").prop("disabled", false).text("Send Message");
            }
        });
    });

    function validateForm(name, email, phone, message, company) {
        if(company.length > 50){
            showResponseMessage("Company name must be less than 50 characters.", "error");
            return false;
        }
        if (name.length < 3 || name.length > 50) {
            showResponseMessage("Name must be at least 3 characters long and maximum 50 characters long.", "error");
            return false;
        }
        if (!validateEmail(email) || email.length > 100) {
            showResponseMessage("Invalid email format.", "error");
            return false;
        }
        if (!validatePhone(phone)) {
            showResponseMessage("Invalid phone number format. Please enter a valid number.", "error");
            return false;
        }
        if (message.length < 10 || message.length > 1000) {
            showResponseMessage("Message must be at least 10 characters long and maximum 1000 characters long.", "error");
            return false;
        }
        return true;
    }

    function validateEmail(email) {
        var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailPattern.test(email);
    }

    function validatePhone(phone) {
        var phonePattern = /^[0-9]{10,15}$/; // Allows numbers with length 10 to 15
        return phonePattern.test(phone);
    }

    function showResponseMessage(message, type) {
        var messageDiv = $("#responseMessage");
        messageDiv.removeClass("hidden").text(message);

        if (type === "success") {
            messageDiv.addClass("bg-green-500").removeClass("bg-red-500");
        } else {
            messageDiv.addClass("bg-red-500").removeClass("bg-green-500");
        }
    }
});