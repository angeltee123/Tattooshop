// Pre-prepared RegEx expressions
const letters = /^[a-zA-Z]+$\p{L}/;
const numbers = /\d+/;
const special_characters = /[`!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?~]/;
const email_format = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
const time = /^([0-1]?[0-9]|2[0-4]):([0-5][0-9])(:[0-5][0-9])?$/;

// get date today
const today = new Date();
today.setHours(0,0,0,0);

// Bootstrap tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl){
  return new bootstrap.Tooltip(tooltipTriggerEl)
});