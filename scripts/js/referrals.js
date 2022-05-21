// submit button
var update_referrals = document.getElementById('update_referrals');

// referrals tab controls
var all_referrals_selected = false;
var select_all_referrals = document.getElementById('Orders__controls__select-all-referrals');
var referral_checkboxes = document.getElementsByClassName('Orders__referral__checkbox');

// referrals fields
var referral_fname = Array.from(document.getElementsByName('referral_fname[]'));
var referral_mi = Array.from(document.getElementsByName('referral_mi[]'));
var referral_lname = Array.from(document.getElementsByName('referral_lname[]'));
var referral_age = Array.from(document.getElementsByName('referral_age[]'));
var referral_email = Array.from(document.getElementsByName('referral_email[]'));
var referral_contact = Array.from(document.getElementsByName('referral_contact_no[]'));

// referrals fields feedback
var referral_fname_err = Array.from(document.getElementsByClassName('referral_fname_err'));
var referral_mi_err = Array.from(document.getElementsByClassName('referral_mi_err'));
var referral_lname_err = Array.from(document.getElementsByClassName('referral_lname_err'));
var referral_age_err = Array.from(document.getElementsByClassName('referral_age_err'));
var referral_email_err = Array.from(document.getElementsByClassName('referral_email_err'));
var referral_contact_err = Array.from(document.getElementsByClassName('referral_contact_err'));

// error reporting
var referrals_errors = [];

// selecting all referrals
select_all_referrals.addEventListener('click', function(){
  all_referrals_selected = !all_referrals_selected;
  all_referrals_selected ? this.innerText = "Deselect" : this.innerText = "Select All";

  for(var i=0, count=referral_checkboxes.length; i < count; i++){
    referral_checkboxes[i].checked = !all_referrals_selected;
    referral_checkboxes[i].click();
  }
});

// switching between tabs
orders_tab.addEventListener('click', function(){
  select_all_referrals.classList.replace('d-inline-block', 'd-none');
});

referrals_tab.addEventListener('click', function(){
  select_all_referrals.classList.replace('d-none', 'd-inline-block');
});

// error reporting
page_form.addEventListener('change', function(){
  referrals_errors.length = 0;

  // referral fields form validation
  for(let x = 0, count = referral_fname.length; x < count; x++){
    if(referral_checkboxes[x].checked === true){
      // validate referral first name
      let value = referral_fname[x].value.toString().replace(/\s*/g,"").trim();
      let message = referral_fname_err[x].lastChild;
      if(value.length == 0 || value.length < 2 || special_characters.test(value) === true || numbers.test(value) === true){
        referral_fname[x].classList.remove('is-valid');
        referral_fname[x].classList.add('is-invalid');

        if (value.length == 0) message.innerText = "First name is required.";
        else if(value.length < 2) message.innerText = "First name must be at least 2 characters long.";
        else message.innerText = "First name must not contain any numbers or special characters.";

        referral_fname_err[x].classList.replace('d-none', 'd-flex');
        referrals_errors.push(message.innerText || message.innerHTML);
      } else {
        referral_fname[x].classList.remove('is-invalid');
        referral_fname[x].classList.add('is-valid');
        referral_fname_err[x].classList.replace('d-flex', 'd-none');
      }

      // validate referral middle initials
      value = referral_mi[x].value.toString().trim();
      message = referral_mi_err[x].lastChild;
      if(value.length > 0 && value.length > 2 || special_characters.test(value) === true || numbers.test(value) === true){
        referral_mi[x].classList.remove('is-valid');
        referral_mi[x].classList.add('is-invalid');

        (value.length > 2) ? message.innerText = "Middle initial must not exceed 2 characters." : message.innerText = "Middle initial must not contain any numbers, spaces or special characters.";

        referral_mi_err[x].classList.replace('d-none', 'd-flex');
        referals_errors.push(message.innerText || message.innerHTML);
      } else {
        referral_mi[x].classList.remove('is-invalid');
        referral_mi[x].classList.add('is-valid');
        referral_mi_err[x].classList.replace('d-flex', 'd-none');
      }

      // validate referral last name
      value = referral_lname[x].value.toString().replace(/\s*/g,"").trim();
      message = referral_lname_err[x].lastChild;
      if(value.length == 0 || value.length < 2 || special_characters.test(value) === true || numbers.test(value) === true){
        referral_lname[x].classList.remove('is-valid');
        referral_lname[x].classList.add('is-invalid');

        if (value.length == 0) message.innerText = "Last name is required.";
        else if(value.length < 2) message.innerText = "Last name must be at least 2 characters long.";
        else message.innerText = "Last name must not contain any numbers or special characters.";

        referral_lname_err[x].classList.replace('d-none', 'd-flex');
        referals_errors.push(message.innerText || message.innerHTML);
      } else {
        referral_lname[x].classList.remove('is-invalid');
        referral_lname[x].classList.add('is-valid');
        referral_lname_err[x].classList.replace('d-flex', 'd-none');
      }

      // validate referral age
      value = parseFloat(referral_age[x].value);
      message = referral_age_err[x].lastChild;
      if(!Number.isInteger(value) || value < parseInt(age.min)){
        referral_age[x].classList.remove('is-valid');
        referral_age[x].classList.add('is-invalid');
        
        message.innerText = (!Number.isInteger(value)) ? "Referral age must be a whole number." : "Referral must be at least 18 years old.";
        
        referral_age_err[x].classList.replace('d-none', 'd-flex');
        referrals_errors.push(message.innerText || message.innerHTML);
      } else {
        referral_age[x].classList.remove('is-invalid');
        referral_age[x].classList.add('is-valid');
        referral_age_err[x].classList.replace('d-flex', 'd-none');
      }

      // validate referral email
      value = referral_email[x].value.toString().trim();
      message = referral_email_err[x].lastChild;
      if(value.length == 0 || !email_format.test(value) === true){
        referral_email[x].classList.remove('is-valid');
        referral_email[x].classList.add('is-invalid');

        (value.length == 0) ? message.innerText = "Email is required." : message.innerText = "Invalid email format.";

        referral_email_err[x].classList.replace('d-none', 'd-flex');
        referrals_errors.push(message.innerText || message.innerHTML);
      } else {
        referral_email[x].classList.remove('is-invalid');
        referral_email[x].classList.add('is-valid');
        referral_email_err[x].classList.replace('d-flex', 'd-none');
      }

      // validate referral contact number
      value = referral_contact[x].value.toString().trim();
      message = referral_contact_err[x].lastChild;
      if(value.length == 0 || value.length < 7 || letters.test(value) === true || !Number.isInteger(value * 1)){
        referral_contact[x].classList.remove('is-valid');
        referral_contact[x].classList.add('is-invalid');

        if (value.length == 0) message.innerText = "Contact number is required.";
        else if(value.length < 7) message.innerText = "Contact number must be at least 7 numbers long.";
        else message.innerText = "Contact number consist of whole numbers.";

        referral_contact_err[x].classList.replace('d-none', 'd-flex');
        referrals_errors.push(message.innerText || message.innerHTML);
      } else {
        referral_contact[x].classList.remove('is-invalid');
        referral_contact[x].classList.add('is-valid');
        referral_contact_err[x].classList.replace('d-flex', 'd-none');
      }
    } else {
      referral_fname[x].classList.contains('is-invalid') ? referral_fname[x].classList.remove('is-invalid') : referral_fname[x].classList.remove('is-valid');
      referral_fname_err[x].classList.replace('d-flex', 'd-none');

      referral_mi[x].classList.contains('is-invalid') ? referral_mi[x].classList.remove('is-invalid') : referral_mi[x].classList.remove('is-valid');
      referral_mi_err[x].classList.replace('d-flex', 'd-none');

      referral_lname[x].classList.contains('is-invalid') ? referral_lname[x].classList.remove('is-invalid') : referral_lname[x].classList.remove('is-valid');
      referral_lname_err[x].classList.replace('d-flex', 'd-none');

      referral_age[x].classList.contains('is-invalid') ? referral_age[x].classList.remove('is-invalid') : referral_age[x].classList.remove('is-valid');
      referral_age_err[x].classList.replace('d-flex', 'd-none');

      referral_email[x].classList.contains('is-invalid') ? referral_email[x].classList.remove('is-invalid') : referral_email[x].classList.remove('is-valid');
      referral_email_err[x].classList.replace('d-flex', 'd-none');

      referral_contact[x].classList.contains('is-invalid') ? referral_contact[x].classList.remove('is-invalid') : referral_contact[x].classList.remove('is-valid');
      referral_contact_err[x].classList.replace('d-flex', 'd-none');
    }
  }

  // submission guard
  update_referrals.disabled = (referrals_errors.length > 0) ? true : false;
});