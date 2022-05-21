// booking form
var booking_form = document.getElementById('New-booking__form');

// booking form submit button
var book = document.getElementById('book');

// booking form fields
var quantity = document.getElementById('quantity');
var service_type = document.getElementById('service_type');
var address = document.getElementById('address');
var scheduled_date = document.getElementById('scheduled_date');
var scheduled_time = document.getElementById('scheduled_time');

// booking form fields feedback
var quantity_err = document.getElementById('quantity_err');
var address_err = document.getElementById('address_err');
var scheduled_date_err = document.getElementById('scheduled_date_err');
var scheduled_time_err = document.getElementById('scheduled_time_err');

// error reporting
var errors = [];

// disabling address field based on service type
service_type.addEventListener('change', function(){
  if(service_type.value.localeCompare("Walk-in") == 0){
    address.readOnly = true;
    address.value = "Mandaue City, Cebu";
  } else {
    address.readOnly = false;
    address.value = "";
  }
});

// error reporting
booking_form.addEventListener('change', function(){
  errors.length = 0;

  // validate quantity
  let value = parseFloat(quantity.value);
  let message = quantity_err.lastChild;
  if(!Number.isInteger(value) || value < parseInt(quantity.min) || value > parseInt(quantity.max)){
    quantity.classList.remove('is-valid');
    quantity.classList.add('is-invalid');

    if (!Number.isInteger(value)) message.innerText = "Quantity must be a whole number.";
    else if(value < parseInt(quantity.min)) message.innerText = "Quantity must not be less than 1.";
    else message.innerText = "Quantity to reserve must not exceed the quantity of this item in your order.";
    
    quantity_err.classList.replace('d-none', 'd-flex');
    errors.push(message.innerText || message.innerHTML);
  } else {
    quantity.classList.remove('is-invalid');
    quantity.classList.add('is-valid');
    quantity_err.classList.replace('d-flex', 'd-none');
  }

  // validate scheduled date
  value = scheduled_date.value.trim();
  message = scheduled_date_err.lastChild;
  let date = value;
  if (isNaN(Date.parse(date)) == true || new Date(date) <= today) {
    scheduled_date.classList.remove('is-valid');
    scheduled_date.classList.add('is-invalid');
    
    message.innerText = (isNaN(Date.parse(date)) == true) ? "Valid date required." : "Date should not be current or past dates.";

    scheduled_date_err.classList.replace('d-none', 'd-flex');
    errors.push(message.innerText || message.innerHTML);
  } else {
    scheduled_date.classList.remove('is-invalid');
    scheduled_date.classList.add('is-valid');
    scheduled_date_err.classList.replace('d-flex', 'd-none');
  }

  // validate scheduled time
  value = scheduled_time.value.trim();
  let hours = parseInt(value.substring(0, 2));
  let minutes = parseInt(value.split(":").pop());
  message = scheduled_time_err.lastChild;
  if(numbers.test(value) == false || hours < 0 || hours > 24 || minutes < 0 || minutes > 60){
    scheduled_time.classList.remove('is-valid');
    scheduled_time.classList.add('is-invalid');

    message.innerText = "Valid time format required.";

    scheduled_time_err.classList.replace('d-none', 'd-flex');
    errors.push(message.innerText || message.innerHTML);
  } else {
    scheduled_time.classList.remove('is-invalid');
    scheduled_time.classList.add('is-valid');
    scheduled_time_err.classList.replace('d-flex', 'd-none');
  }

  // validate service location
  value = address.value.toString().trim();
  message = address_err.lastChild;
  if (value.length == 0) {
    address.classList.remove('is-valid');
    address.classList.add('is-invalid');
    
    message.innerText = "Address is required.";

    address_err.classList.replace('d-none', 'd-flex');
    errors.push(message.innerText || message.innerHTML);
  } else {
    address.classList.remove('is-invalid');
    address.classList.add('is-valid');
    address_err.classList.replace('d-flex', 'd-none');
  }

  // submission guard
  book.disabled = (errors.length > 0) ? true : false;
});