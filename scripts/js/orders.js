// submit buttons
var update_items = document.getElementById('update_items');
var refer = document.getElementById('refer');

// orders tab controls
var all_orders_selected = false;
var select_all_items = document.getElementById('controls__select-all-orders');
var item_checkboxes =document.getElementsByClassName('Orders__order-item__checkbox');

// item fields
var quantity_fields = Array.from(document.getElementsByName('quantity[]'));
var width_fields = Array.from(document.getElementsByName('width[]'));
var height_fields = Array.from(document.getElementsByName('height[]'));

// item fields feedback
var quantity_err = Array.from(document.getElementsByClassName('quantity_err'));
var width_err = Array.from(document.getElementsByClassName('width_err'));
var height_err = Array.from(document.getElementsByClassName('height_err'));

// collapsible referral form fields
var first_name = document.getElementById('first_name');
var mi = document.getElementById('mi');
var last_name = document.getElementById('last_name');
var age = document.getElementById('age');
var email = document.getElementById('email');
var contact_number = document.getElementById('contact_number');

// collapsible referral form fields feedback
var first_name_err = document.getElementById('first_name_err');
var mi_err = document.getElementById('mi_err');
var last_name_err = document.getElementById('last_name_err');
var age_err = document.getElementById('age_err');
var email_err = document.getElementById('email_err');
var contact_number_err = document.getElementById('contact_number_err');

// error reporting
var items_errors = [];
var refer_errors = [];

// selecting all items
select_all_items.addEventListener('click', function(){
  all_orders_selected = !all_orders_selected;
  all_orders_selected ? this.innerText = "Deselect" : this.innerText = "Select All";

  for(var i=0, count=item_checkboxes.length; i < count; i++){
    item_checkboxes[i].checked = !all_orders_selected;
    item_checkboxes[i].click();
  }
});

// switching between tabs
orders_tab.addEventListener('click', function(){
  select_all_items.classList.replace('d-none', 'd-inline-block');
});

referrals_tab.addEventListener('click', function(){
  select_all_items.classList.replace('d-inline-block', 'd-none');
});

// error reporting
page_form.addEventListener('change', function(){
  items_errors.length = 0; 
  refer_errors.length = 0;

  // item fields validation
  for(let x = 0, count = quantity_fields.length; x < count; x++){
    if(item_checkboxes[x].checked === true){
      // validate item quantity
      let value = parseFloat(quantity_fields[x].value);
      let message = quantity_err[x].lastChild;
      if(!Number.isInteger(value) || value < parseInt(quantity_fields[x].min)){
        quantity_fields[x].classList.remove('is-valid');
        quantity_fields[x].classList.add('is-invalid');
        
        message.innerText = (!Number.isInteger(value)) ? "Quantity must be a whole number." : "Quantity must not be less than 1.";
        
        quantity_err[x].classList.replace('d-none', 'd-flex');
        items_errors.push(message.innerText || message.innerHTML);
      } else {
        quantity_fields[x].classList.remove('is-invalid');
        quantity_fields[x].classList.add('is-valid');
        quantity_err[x].classList.replace('d-flex', 'd-none');
      }

      // validate item width
      value = parseFloat(width_fields[x].value);
      message = width_err[x].lastChild;
      if(!Number.isInteger(value) || value < parseInt(width_fields[x].min) || value > parseInt(width_fields[x].max)){
        width_fields[x].classList.remove('is-valid');
        width_fields[x].classList.add('is-invalid');

        if (!Number.isInteger(value)) message.innerText = "Width must be a whole number.";
        else if(value < parseInt(width_fields[x].min)) message.innerText = "Width must not be less than 1 inch.";
        else message.innerText = "Width must not exceed " + width_fields[x].max + " inches.";

        width_err[x].classList.replace('d-none', 'd-flex');
        items_errors.push(message.innerText || message.innerHTML);
      } else {
        width_fields[x].classList.remove('is-invalid');
        width_fields[x].classList.add('is-valid');
        width_err[x].classList.replace('d-flex', 'd-none');
      }

      // validate item height
      value = parseFloat(height_fields[x].value);
      message = height_err[x].lastChild;
      if(!Number.isInteger(value) || value < parseInt(height_fields[x].min) || value > parseInt(height_fields[x].max)){
        height_fields[x].classList.remove('is-valid');
        height_fields[x].classList.add('is-invalid');

        if (!Number.isInteger(value)) message.innerText = "Height must be a whole number.";
        else if(value < parseInt(height_fields[x].min)) message.innerText = "Height must not be less than 1 inch.";
        else message.innerText = "Height must not exceed " + height_fields[x].max + " inches.";

        height_err[x].classList.replace('d-none', 'd-flex');
        items_errors.push(message.innerText || message.innerHTML);
      } else {
        height_fields[x].classList.remove('is-invalid');
        height_fields[x].classList.add('is-valid');
        height_err[x].classList.replace('d-flex', 'd-none');
      }
    } else {
      quantity_fields[x].classList.contains('is-invalid') ? quantity_fields[x].classList.remove('is-invalid') : quantity_fields[x].classList.remove('is-valid');
      quantity_err[x].classList.replace('d-flex', 'd-none');
  
      width_fields[x].classList.contains('is-invalid') ? width_fields[x].classList.remove('is-invalid') : width_fields[x].classList.remove('is-valid');
      width_err[x].classList.replace('d-flex', 'd-none');

      height_fields[x].classList.contains('is-invalid') ? height_fields[x].classList.remove('is-invalid') : height_fields[x].classList.remove('is-valid');
      height_err[x].classList.replace('d-flex', 'd-none');
    }
  }

  // submission guard
  update_items.disabled = (items_errors.length > 0) ? true : false;

  // collapsible referral form validation
  // validate referral first name
  let value = first_name.value.toString().replace(/\s*/g,"").trim();
  let message = first_name_err.lastChild;
  if(value.length == 0 || value.length < 2 || special_characters.test(value) === true || numbers.test(value) === true){
    first_name.classList.remove('is-valid');
    first_name.classList.add('is-invalid');

    if (value.length == 0) message.innerText = "First name is required.";
    else if(value.length < 2) message.innerText = "First name must be at least 2 characters long.";
    else message.innerText = "First name must not contain any numbers or special characters.";

    first_name_err.classList.replace('d-none', 'd-flex');
    refer_errors.push(message.innerText || message.innerHTML);
  } else {
    first_name.classList.remove('is-invalid');
    first_name.classList.add('is-valid');
    first_name_err.classList.replace('d-flex', 'd-none');
  }

  // validate referral middle initials
  value = mi.value.toString().trim();
  message = mi_err.lastChild;
  if(value.length > 0 && value.length > 2 || special_characters.test(value) === true || numbers.test(value) === true){
    mi.classList.remove('is-valid');
    mi.classList.add('is-invalid');

    (value.length > 2) ? message.innerText = "Middle initial must not exceed 2 characters." : message.innerText = "Middle initial must not contain any numbers, spaces or special characters.";

    mi_err.classList.replace('d-none', 'd-flex');
    refer_errors.push(message.innerText || message.innerHTML);
  } else {
    mi.classList.remove('is-invalid');
    mi.classList.add('is-valid');
    mi_err.classList.replace('d-flex', 'd-none');
  }

  // validate referral last name
  value = last_name.value.toString().replace(/\s*/g,"").trim();
  message = last_name_err.lastChild;
  if(value.length == 0 || value.length < 2 || special_characters.test(value) === true || numbers.test(value) === true){
    last_name.classList.remove('is-valid');
    last_name.classList.add('is-invalid');

    if (value.length == 0) message.innerText = "Last name is required.";
    else if(value.length < 2) message.innerText = "Last name must be at least 2 characters long.";
    else message.innerText = "Last name must not contain any numbers or special characters.";

    last_name_err.classList.replace('d-none', 'd-flex');
    refer_errors.push(message.innerText || message.innerHTML);
  } else {
    last_name.classList.remove('is-invalid');
    last_name.classList.add('is-valid');
    last_name_err.classList.replace('d-flex', 'd-none');
  }
  
  // validate referral age
  value = parseFloat(age.value);
  message = age_err.lastChild;
  if(!Number.isInteger(value) || value < parseInt(age.min)){
    age.classList.remove('is-valid');
    age.classList.add('is-invalid');
    
    message.innerText = (!Number.isInteger(value)) ? "Referral age must be a whole number." : "Referral must be at least 18 years old.";
    
    age_err.classList.replace('d-none', 'd-flex');
    refer_errors.push(message.innerText || message.innerHTML);
  } else {
    age.classList.remove('is-invalid');
    age.classList.add('is-valid');
    age_err.classList.replace('d-flex', 'd-none');
  }

  // validate referral email
  value = email.value.toString().trim();
  message = email_err.lastChild;
  if(value.length == 0 || !email_format.test(value) === true){
    email.classList.remove('is-valid');
    email.classList.add('is-invalid');

    (value.length == 0) ? message.innerText = "Email is required." : message.innerText = "Invalid email format.";

    email_err.classList.replace('d-none', 'd-flex');
    refer_errors.push(message.innerText || message.innerHTML);
  } else {
    email.classList.remove('is-invalid');
    email.classList.add('is-valid');
    email_err.classList.replace('d-flex', 'd-none');
  }

  // validate referral contact number
  value = contact_number.value.toString().trim();
  message = contact_number_err.lastChild;
  if(value.length == 0 || value.length < 7 || letters.test(value) === true || !Number.isInteger(value * 1)){
    contact_number.classList.remove('is-valid');
    contact_number.classList.add('is-invalid');

    if (value.length == 0) message.innerText = "Contact number is required.";
    else if(value.length < 7) message.innerText = "Contact number must be at least 7 numbers long.";
    else message.innerText = "Contact number consist of whole numbers.";

    contact_number_err.classList.replace('d-none', 'd-flex');
    refer_errors.push(message.innerText || message.innerHTML);
  } else {
    contact_number.classList.remove('is-invalid');
    contact_number.classList.add('is-valid');
    contact_number_err.classList.replace('d-flex', 'd-none');
  }

  // submission guard
  refer.disabled = (refer_errors.length > 0) ? true : false;
});