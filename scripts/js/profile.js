// page forms
var account_details_form = document.getElementById('Profile__account-details-form');
var change_password_form = document.getElementById('Profile__change-password-form');

// submit buttons
var update_profile = document.getElementById('update_profile');
var update_password = document.getElementById('update_password');

// tabs
var tab__account_details = document.getElementById('tabs--account-details');
var tab__change_password = document.getElementById('tabs--change-password');

// profile sections
var profile__account_details = document.getElementById('Profile__account-details');
var profile__change_password = document.getElementById('Profile__change-password');
var account_form_is_active = true;
var password_form_is_active = false;

// account details fields
var first_name = document.getElementById('first_name');
var mi = document.getElementById('mi');
var last_name = document.getElementById('last_name');
var address = document.getElementById('address');
var contact_number = document.getElementById('contact_number');
var email = document.getElementById('email');
var birthdate = document.getElementById('birthdate');
var image = document.getElementById('image');

// account details fields feedback
var first_name_err = document.getElementById('first_name_err');
var mi_err = document.getElementById('mi_err');
var last_name_err = document.getElementById('last_name_err');
var address_err = document.getElementById('address_err');
var contact_number_err = document.getElementById('contact_number_err');
var email_err = document.getElementById('email_err');
var birthdate_err = document.getElementById('birthdate_err');

// change password fields
var password = document.getElementById('password');
var new_password = document.getElementById('new_password');
var confirm_password = document.getElementById('confirm_password');

// change password fields feedback
var password_err = document.getElementById('password_err');
var new_password_err = document.getElementById('new_password_err');
var confirm_password_err = document.getElementById('confirm_password_err');

// error reporting
var account_details_errors = [];
var password_errors = [];

// account details tab
tab__account_details.addEventListener('click', function(){
  profile__account_details.classList.replace('d-none', 'd-block');
  profile__change_password.classList.replace('d-block', 'd-none');

  account_form_is_active = true;
  password_form_is_active = false;

  birthdate.required = email.required = contact_number.required =
  address.required = last_name.required = first_name.required = true;

  confirm_password.required = new_password.required = password.required = false;
});

// change password tab
tab__change_password.addEventListener('click', function(){
  profile__account_details.classList.replace('d-block', 'd-none');
  profile__change_password.classList.replace('d-none', 'd-block');

  account_form_is_active = false;
  password_form_is_active = true;

  birthdate.required = email.required = contact_number.required =
  address.required = last_name.required = first_name.required = false;

  confirm_password.required = new_password.required = password.required = true;
});

// error reporting
account_details_form.addEventListener('change', function(){
  if(account_form_is_active == true){
    account_details_errors.length = 0;

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
      account_details_errors.push(message.innerText || message.innerHTML);
    } else {
      first_name.classList.remove('is-invalid');
      first_name.classList.add('is-valid');
      first_name_err.classList.replace('d-flex', 'd-none');
    }

    // validate middle initials
    value = mi.value.toString().trim();
    message = mi_err.lastChild;
    if(value.length > 0 && value.length > 2 || special_characters.test(value) === true || numbers.test(value) === true){
      mi.classList.remove('is-valid');
      mi.classList.add('is-invalid');

      (value.length > 2) ? message.innerText = "Middle initial must not exceed 2 characters." : message.innerText = "Middle initial must not contain any numbers, spaces or special characters.";

      mi_err.classList.replace('d-none', 'd-flex');
      account_details_errors.push(message.innerText || message.innerHTML);
    } else {
      mi.classList.remove('is-invalid');
      mi.classList.add('is-valid');
      mi_err.classList.replace('d-flex', 'd-none');
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
      account_details_errors.push(message.innerText || message.innerHTML);
    } else {
      last_name.classList.remove('is-invalid');
      last_name.classList.add('is-valid');
      last_name_err.classList.replace('d-flex', 'd-none');
    }

    // validate address
    value = address.value.toString().trim();
    message = address_err.lastChild;
    if (value.length == 0) {
      address.classList.remove('is-valid');
      address.classList.add('is-invalid');
      
      message.innerText = "Address is required.";

      address_err.classList.replace('d-none', 'd-flex');
      account_details_errors.push(message.innerText || message.innerHTML);
    } else {
      address.classList.remove('is-invalid');
      address.classList.add('is-valid');
      address_err.classList.replace('d-flex', 'd-none');
    }

    // validate contact number
    value = contact_number.value.toString().trim();
    message = contact_number_err.lastChild;
    if(value.length == 0 || value.length < 7 || letters.test(value) === true || !Number.isInteger(value * 1)){
      contact_number.classList.remove('is-valid');
      contact_number.classList.add('is-invalid');
  
      if (value.length == 0) message.innerText = "Contact number is required.";
      else if(value.length < 7) message.innerText = "Contact number must be at least 7 numbers long.";
      else message.innerText = "Contact number consist of whole numbers.";
  
      contact_number_err.classList.replace('d-none', 'd-flex');
      account_details_errors.push(message.innerText || message.innerHTML);
    } else {
      contact_number.classList.remove('is-invalid');
      contact_number.classList.add('is-valid');
      contact_number_err.classList.replace('d-flex', 'd-none');
    }

    // validate email
    value = email.value.toString().trim();
    message = email_err.lastChild;
    if(value.length == 0 || !email_format.test(value) === true){
      email.classList.remove('is-valid');
      email.classList.add('is-invalid');

      (value.length == 0) ? message.innerText = "Email is required." : message.innerText = "Invalid email format.";

      email_err.classList.replace('d-none', 'd-flex');
      account_details_errors.push(message.innerText || message.innerHTML);
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
      account_details_errors.push(message.innerText || message.innerHTML);
    } else {
      birthdate.classList.remove('is-invalid');
      birthdate.classList.add('is-valid');
      birthdate_err.classList.replace('d-flex', 'd-none');
    }

    // submission guard
    update_profile.disabled = (account_details_errors.length > 0) ? true : false;
  }
});

change_password_form.addEventListener('change', function(){
  if(password_form_is_active == true){
    password_errors.length = 0;

  // validate new password
  new_password.input = "text";
  value = new_password.value.toString().trim();
  new_password.input = "password";
  message = new_password_err.lastChild;
  if (value.length == 0 || special_characters.test(value) === false || capital_letters.test(value) === false || numbers.test(value) === false) {
    new_password.classList.remove('is-valid');
    new_password.classList.add('is-invalid');
    
    if (value.length == 0) message.innerText = "Password is required.";
    else if(special_characters.test(value) === false) message.innerText = "Password must contain at least one special character.";
    else if(capital_letters.test(value) === false) message.innerText = "Password must contain at least one capital letter.";
    else message.innerText = "Password must contain at least one numeric character.";

    new_password_err.classList.replace('d-none', 'd-flex');
    password_errors.push(message.innerText || message.innerHTML);
  } else {
    new_password.classList.remove('is-invalid');
    new_password.classList.add('is-valid');
    new_password_err.classList.replace('d-flex', 'd-none');
  }

    // validate password
    confirm_password.input = new_password.input = "text";
    value = confirm_password.value.toString().trim();
    let match_password = new_password.value.toString().trim();
    confirm_password.input = new_password.input = "password";
    message = confirm_password_err.lastChild;
    if (value.length == 0 || value.localeCompare(match_password) != 0) {
      confirm_password.classList.remove('is-valid');
      confirm_password.classList.add('is-invalid');
      
      message.innerText = (value.length == 0) ? "Confirm password field must not be empty." : "Passwords must match.";

      confirm_password_err.classList.replace('d-none', 'd-flex');
      password_errors.push(message.innerText || message.innerHTML);
    } else {
      confirm_password.classList.remove('is-invalid');
      confirm_password.classList.add('is-valid');
      confirm_password_err.classList.replace('d-flex', 'd-none');
    }

    // validate old password
    password.input = "text";
    value = password.value.toString().trim();
    password.input = "password";
    message = password_err.lastChild;
    if(value.length == 0){
      password.classList.remove('is-valid');
      password.classList.add('is-invalid');

      message.innerText = "Old password is required.";

      password_err.classList.replace('d-none', 'd-flex');
      password_errors.push(message.innerText || message.innerHTML);
    } else {
      password.classList.remove('is-invalid');
      password.classList.add('is-valid');
      password_err.classList.replace('d-flex', 'd-none');
    }

    // submission guard
    update_password.disabled = (password_errors.length > 0) ? true : false;
  }
});
