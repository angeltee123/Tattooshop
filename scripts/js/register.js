// registration form
var registration_form = document.getElementById('Registration__form');

// submit button
var register = document.getElementById("signup");

// registration fields
var first_name = document.getElementById('first_name');
var last_name = document.getElementById('last_name');
var email = document.getElementById('email');
var birthdate = document.getElementById('birthdate');
var password = document.getElementById('password');
var confirm_password = document.getElementById('confirm_password');
var tos_checkbox = document.getElementById("tos_agreed");

// registration fields feedback
var first_name_err = document.getElementById('first_name_err');
var last_name_err = document.getElementById('last_name_err');
var email_err = document.getElementById('email_err');
var birthdate_err = document.getElementById('birthdate_err');
var password_err = document.getElementById('password_err');
var confirm_password_err = document.getElementById('confirm_password_err');

// error reporting
var errors = [];

registration_form.addEventListener('change', function(){
  errors.length = 0;

  // validate first name
  let value = first_name.value.toString().replace(/\s*/g,"").trim();
  let message = first_name_err.lastChild;
  if(value.length == 0 || value.length < 2 || special_characters.test(value) === true || numbers.test(value) === true){
    first_name.classList.remove('is-valid');
    first_name.classList.add('is-invalid');

    if (value.length == 0) message.innerText = "First name is required.";
    else if(value.length < 2) message.innerText = "First name must be at least 2 characters long.";
    else message.innerText = "First name must not contain any numbers or special characters.";

    first_name_err.classList.replace('d-none', 'd-flex');
    errors.push(message.innerText || message.innerHTML);
  } else {
    first_name.classList.remove('is-invalid');
    first_name.classList.add('is-valid');
    first_name_err.classList.replace('d-flex', 'd-none');
  }

  // validate last name
  value = last_name.value.toString().replace(/\s*/g,"").trim();
  message = last_name_err.lastChild;
  if(value.length == 0 || value.length < 2 || special_characters.test(value) === true || numbers.test(value) === true){
    last_name.classList.remove('is-valid');
    last_name.classList.add('is-invalid');

    if (value.length == 0) message.innerText = "Last name is required.";
    else if(value.length < 2) message.innerText = "Last name must be at least 2 characters long.";
    else message.innerText = "Last name must not contain any numbers or special characters.";

    last_name_err.classList.replace('d-none', 'd-flex');
    errors.push(message.innerText || message.innerHTML);
  } else {
    last_name.classList.remove('is-invalid');
    last_name.classList.add('is-valid');
    last_name_err.classList.replace('d-flex', 'd-none');
  }

  // validate email
  value = email.value.toString().trim();
  message = email_err.lastChild;
  if(value.length == 0 || !email_format.test(value) === true){
    email.classList.remove('is-valid');
    email.classList.add('is-invalid');

    (value.length == 0) ? message.innerText = "Email is required." : message.innerText = "Invalid email format.";

    email_err.classList.replace('d-none', 'd-flex');
    errors.push(message.innerText || message.innerHTML);
  } else {
    email.classList.remove('is-invalid');
    email.classList.add('is-valid');
    email_err.classList.replace('d-flex', 'd-none');
  }

  // validate birthdate
  value = birthdate.value.trim();
  message = birthdate_err.lastChild;
  let date = value;
  if (isNaN(Date.parse(date)) == true || new Date(date) >= today) {
    birthdate.classList.remove('is-valid');
    birthdate.classList.add('is-invalid');
    
    message.innerText = (isNaN(Date.parse(date)) == true) ? "Valid date required." : "Birthdate should not be current or future dates.";

    birthdate_err.classList.replace('d-none', 'd-flex');
    errors.push(message.innerText || message.innerHTML);
  } else {
    birthdate.classList.remove('is-invalid');
    birthdate.classList.add('is-valid');
    birthdate_err.classList.replace('d-flex', 'd-none');
  }

  // validate password
  password.input = "text";
  value = password.value.toString().trim();
  password.input = "password";
  message = password_err.lastChild;
  if (value.length == 0 || special_characters.test(value) === false || capital_letters.test(value) === false || numbers.test(value) === false) {
    password.classList.remove('is-valid');
    password.classList.add('is-invalid');
    
    if (value.length == 0) message.innerText = "Password is required.";
    else if(special_characters.test(value) === false) message.innerText = "Password must contain at least one special character.";
    else if(capital_letters.test(value) === false) message.innerText = "Password must contain at least one capital letter.";
    else message.innerText = "Password must contain at least one numeric character.";

    password_err.classList.replace('d-none', 'd-flex');
    errors.push(message.innerText || message.innerHTML);
  } else {
    password.classList.remove('is-invalid');
    password.classList.add('is-valid');
    password_err.classList.replace('d-flex', 'd-none');
  }

  // validate password
  confirm_password.input = password.input = "text";
  value = confirm_password.value.toString().trim();
  let match_password = password.value.toString().trim();
  confirm_password.input = password.input = "password";
  message = confirm_password_err.lastChild;
  if (value.length == 0 || value.localeCompare(match_password) != 0) {
    confirm_password.classList.remove('is-valid');
    confirm_password.classList.add('is-invalid');
    
    message.innerText = (value.length == 0) ? "Confirm password field must not be empty." : "Passwords must match.";

    confirm_password_err.classList.replace('d-none', 'd-flex');
    errors.push(message.innerText || message.innerHTML);
  } else {
    confirm_password.classList.remove('is-invalid');
    confirm_password.classList.add('is-valid');
    confirm_password_err.classList.replace('d-flex', 'd-none');
  }

  // submission guard
  register.disabled = (errors.length > 0 || tos_checkbox.checked == false) ? true : false;
});