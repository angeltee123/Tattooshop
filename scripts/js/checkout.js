// checkout form
var checkout_form = document.getElementById('Checkout__form');

// checkout item checkboxes
var items = Array.from(document.querySelectorAll('input[type=checkbox].form-check-input'));
var checked = [];

// checkout item fields
var checkout_quantity = Array.from(document.getElementsByName('checkout_quantity[]'));
var quantity = Array.from(document.getElementsByName('quantity[]'));

// billing form fields
var first_name = document.getElementById('first_name');
var last_name = document.getElementById('last_name');
var street_address = document.getElementById('street_address');
var city = document.getElementById('city');
var province = document.getElementById('province');
var zip = document.getElementById('zip');
var amount_paid = document.getElementById('amount_paid');
var payment_method = document.getElementsByName('payment_method');
var card_number = document.getElementById('card_number');
var pin = document.getElementById('pin');
var bank_name = document.getElementById('bank_name');

// checkout form feedback
var quantity_err = Array.from(document.getElementsByClassName('quantity_err'));
var first_name_err = document.getElementById('first_name_err');
var last_name_err = document.getElementById('last_name_err');
var street_address_err = document.getElementById('street_address_err');
var city_err = document.getElementById('city_err');
var province_err = document.getElementById('province_err');
var zip_err = document.getElementById('zip_err');
var amount_paid_err = document.getElementById('amount_paid_err');
var payment_method_err = document.getElementById('payment_method_err');
var card_number_err = document.getElementById('card_number_err');
var pin_err = document.getElementById('pin_err');
var bank_name_err = document.getElementById('bank_name_err');

// getting prices
var prices = Array.from(document.querySelectorAll('.prices'));
var addons = Array.from(document.querySelectorAll('.addons'));

// getting total
var amount_due_total = document.getElementById('total');

// getting checkout submit button
var checkout = document.getElementById('Checkout__checkout');

// extracting total due for each item
for(var i=0, count=items.length; i < count; i++){
  checked = items.map(item => items.indexOf(item));
  prices[i] = parseFloat((prices[i].innerText || textContent).substring(1));
  addons[i] = addons[i].innerText || textContent;
}

// updating amount due total
for(var i=0, item_count=items.length; i < item_count; i++){
  items[i].addEventListener('change', function(){
    item_index = items.indexOf(this);
    
    if(this.checked){
      checked.push(item_index);
    } else {
      var index = checked.indexOf(item_index);
      if(index > -1){
        checked.splice(index, 1);
      }
    }

    total = 0.00;
    for(var j=0, count=checked.length; j < count; j++){
      total+= parseInt(quantity[checked[j]].value) * prices[checked[j]];

      if(!(addons[checked[j]].localeCompare("N/A") == 0)){
        total+= parseFloat(addons[checked[j]].substring(1));
      }        
    }

    if(discounted){
      total -= (total * .15); 
    }

    amount_due_total.innerText = "₱".concat((total.toFixed(2)).toString());
  });
}

// error reporting
var errors = [];

checkout_form.addEventListener('change', function(){
  errors.length = 0;
  
  // checkout fields validation
  for(let x = 0, count=items.length; x < count; x++){
    if(items[x].checked === true){
      // validate checkout quantity
      let value = parseFloat(checkout_quantity[x].value);
      let message = quantity_err[x].lastChild;
      if(!Number.isInteger(value) || value < parseInt(checkout_quantity[x].min) || value > parseInt(checkout_quantity[x].max)){
        checkout_quantity[x].classList.remove('is-valid');
        checkout_quantity[x].classList.add('is-invalid');

        if (!Number.isInteger(value)) message.innerText = "Quantity must be a whole number.";
        else if(value < parseInt(checkout_quantity[x].min)) message.innerText = "Quantity must not be less than 1.";
        else message.innerText = "Quantity to checkout must not exceed the quantity of this item in your order.";
        
        quantity_err[x].classList.replace('d-none', 'd-flex');
        errors.push(message.innerText || message.innerHTML);
      } else {
        checkout_quantity[x].classList.remove('is-invalid');
        checkout_quantity[x].classList.add('is-valid');
        quantity_err[x].classList.replace('d-flex', 'd-none');
      }
    } else {
      checkout_quantity[x].classList.contains('is-invalid') ? checkout_quantity[x].classList.remove('is-invalid') : checkout_quantity[x].classList.remove('is-valid');
      quantity_err[x].classList.replace('d-flex', 'd-none');
    }
  }

  // billing form validation
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

  // validate street address
  value = street_address.value.toString().replace(/\s*/g,"").trim();
  message = street_address_err.lastChild;
  if(value.length == 0){
    street_address.classList.remove('is-valid');
    street_address.classList.add('is-invalid');

    message.innerText = "Street address is required.";

    street_address_err.classList.replace('d-none', 'd-flex');
    errors.push(message.innerText || message.innerHTML);
  } else {
    street_address.classList.remove('is-invalid');
    street_address.classList.add('is-valid');
    street_address_err.classList.replace('d-flex', 'd-none');
  }

  // validate city name
  value = city.value.toString().replace(/\s*/g,"").trim();
  message = city_err.lastChild;
  if(value.length == 0 || value.length < 2 || /[`!@#$%^&*_+\=\[\]{};':"\\|,.<>\/?~]/.test(value) === true || numbers.test(value) === true){
    city.classList.remove('is-valid');
    city.classList.add('is-invalid');

    if (value.length == 0) message.innerText = "City name is required.";
    else if(value.length < 2) message.innerText = "City name must be at least 2 characters long.";
    else message.innerText = "City name must not contain any numbers or special characters.";

    city_err.classList.replace('d-none', 'd-flex');
    errors.push(message.innerText || message.innerHTML);
  } else {
    city.classList.remove('is-invalid');
    city.classList.add('is-valid');
    city_err.classList.replace('d-flex', 'd-none');
  }

  // validate province name
  value = province.value.toString().replace(/\s*/g,"").trim();
  message = province_err.lastChild;
  if(value.length == 0 || value.length < 2 || /[`!@#$%^&*_+\=\[\]{};':"\\|,.<>\/?~]/.test(value) === true || numbers.test(value) === true){
    province.classList.remove('is-valid');
    province.classList.add('is-invalid');

    if (value.length == 0) message.innerText = "Province name is required.";
    else if(value.length < 2) message.innerText = "Province name must be at least 2 characters long.";
    else message.innerText = "Province name must not contain any numbers or special characters.";

    province_err.classList.replace('d-none', 'd-flex');
    errors.push(message.innerText || message.innerHTML);
  } else {
    province.classList.remove('is-invalid');
    province.classList.add('is-valid');
    province_err.classList.replace('d-flex', 'd-none');
  }
  
  // validate zip code
  value = zip.value;
  message = zip_err.lastChild;
  if(value.length == 0 || value.length < 4 || !Number.isInteger(parseFloat(value))){
    zip.classList.remove('is-valid');
    zip.classList.add('is-invalid');

    message.innerText = (value.length == 0) ? "ZIP Code is required." : "ZIP code must be four numbers long.";
    
    zip_err.classList.replace('d-none', 'd-flex');
    errors.push(message.innerText || message.innerHTML);
  } else {
    zip.classList.remove('is-invalid');
    zip.classList.add('is-valid');
    zip_err.classList.replace('d-flex', 'd-none');
  }

  // validate payment amount
  value = parseFloat(amount_paid.value);
  message = amount_paid_err.lastChild;
  if(value == 0 || Number(value) != value || value < total){
    amount_paid.classList.remove('is-valid');
    amount_paid.classList.add('is-invalid');

    if (value.length == 0) message.innerText = "Payment amount is required.";
    else if(Number(value) != value) message.innerText = "Payment amount must be a numeric value.";
    else message.innerText = "Payment amount must be greater than or equal to the amount due total.";
    
    amount_paid_err.classList.replace('d-none', 'd-flex');
    errors.push(message.innerText || message.innerHTML);
  } else {
    amount_paid.classList.remove('is-invalid');
    amount_paid.classList.add('is-valid');
    amount_paid_err.classList.replace('d-flex', 'd-none');
  }

  // validate payment method
  let flag = false;
  message = payment_method_err.lastChild;
  for(let i = 0, radios = payment_method.length; i < radios; i++){
    if(payment_method[i].checked == true){
      flag = true;
    }
  }

  if(flag === true){
    payment_method_err.classList.replace('d-flex', 'd-none');
  } else {
    message.innerText = "Payment method is required.";
    payment_method_err.classList.replace('d-none', 'd-flex');
    errors.push(message.innerText || message.innerHTML);
  }

  // validate card number
  value = card_number.value;
  message = card_number_err.lastChild;
  if(value.length == 0 || value.length < parseInt(card_number.max) || !Number.isInteger(parseFloat(value))){
    card_number.classList.remove('is-valid');
    card_number.classList.add('is-invalid');

    message.innerText = (value.length == 0) ? "Card number is required." : "Card number must at least 11 numbers long.";
    
    card_number_err.classList.replace('d-none', 'd-flex');
    errors.push(message.innerText || message.innerHTML);
  } else {
    card_number.classList.remove('is-invalid');
    card_number.classList.add('is-valid');
    card_number_err.classList.replace('d-flex', 'd-none');
  }

  // validate card pin
  value = pin.value;
  message = pin_err.lastChild;
  if(value.length == 0 || value.length < 6 || !Number.isInteger(parseFloat(value))){
    pin.classList.remove('is-valid');
    pin.classList.add('is-invalid');

    message.innerText = (value.length == 0) ? "Card PIN is required." : "Card PIN code must be six numbers long.";
    
    pin_err.classList.replace('d-none', 'd-flex');
    errors.push(message.innerText || message.innerHTML);
  } else {
    pin.classList.remove('is-invalid');
    pin.classList.add('is-valid');
    pin_err.classList.replace('d-flex', 'd-none');
  }

  // validate bank name
  value = bank_name.value.toString().replace(/\s*/g,"").trim();
  message = bank_name_err.lastChild;
  if(value.length == 0 || value.length < 2 || special_characters.test(value) === true || numbers.test(value) === true){
    bank_name.classList.remove('is-valid');
    bank_name.classList.add('is-invalid');

    if (value.length == 0) message.innerText = "Bank name is required.";
    else if(value.length < 2) message.innerText = "Bank name must be at least 2 characters long.";
    else message.innerText = "Bank name must not contain any numbers or special characters.";

    bank_name_err.classList.replace('d-none', 'd-flex');
    errors.push(message.innerText || message.innerHTML);
  } else {
    bank_name.classList.remove('is-invalid');
    bank_name.classList.add('is-valid');
    bank_name_err.classList.replace('d-flex', 'd-none');
  }

  // submission guard
  checkout.disabled = (errors.length > 0 || checked.length == 0) ? true : false;
});