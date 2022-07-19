// collapsibles
var show_orders = false;
var toggle_orders = document.getElementById('toggle_orders');
var orders = document.getElementsByClassName('Orders__order');
var order_collapsibles = document.getElementsByClassName('Orders__order__collapsible__body');

// tab controls
var all_items_tab = document.getElementById('controls--tab--all-items');
var orders_tab = document.getElementById('controls--tab--orders');
var referrals_tab = document.getElementById('controls--tab--referrals');

// tab sections
var items = document.getElementsByClassName('Orders__order__items');
var referrals = document.getElementsByClassName('Orders__order__referrals');

// collapsibles responsive stacking
for(var i=0, count=orders.length; i < count; i++){
  let order = orders[i];

  order_collapsibles[i].addEventListener('shown.bs.collapse', function (){
    order.classList.replace('my-2', 'my-4');
  });

  order_collapsibles[i].addEventListener('hidden.bs.collapse', function (){
    order.classList.replace('my-4', 'my-2');
  });
}

// toggling all collapsibles
toggle_orders.addEventListener('click', function(){
  show_orders = !show_orders;
  show_orders === true ? toggle_orders.innerText = "Hide All Orders" : toggle_orders.innerText = "Show All Orders";
  
  for(var i=0, count=order_collapsibles.length; i < count; i++){
    if(show_orders === true){
      if(!(order_collapsibles[i].classList.contains('show'))){
        let collapse = new bootstrap.Collapse(order_collapsibles[i], { show: true, hide: false });
      }
    } else {
      if((order_collapsibles[i].classList.contains('show'))){
        let collapse = new bootstrap.Collapse(order_collapsibles[i], { show: false, hide: true });
      }
    }
  }
});

// switching between tabs
all_items_tab.addEventListener('click', function(){
  this.classList.add('controls--tab--active');
  orders_tab.classList.remove('controls--tab--active');
  referrals_tab.classList.remove('controls--tab--active');

  for(var i=0, count=items.length; i < count; i++){
    items[i].classList.replace('d-none', 'd-block');
  }

  for(var i=0, count=referrals.length; i < count; i++){
    referrals[i].classList.remove('d-none', 'd-block');
  }
});

orders_tab.addEventListener('click', function(){
  this.classList.add('controls--tab--active');
  all_items_tab.classList.remove('controls--tab--active');
  referrals_tab.classList.remove('controls--tab--active');

  for(var i=0, count=items.length; i < count; i++){
    items[i].classList.replace('d-none', 'd-block');
  }

  for(var i=0, count=referrals.length; i < count; i++){
    referrals[i].classList.replace('d-block', 'd-none');
  }
});

referrals_tab.addEventListener('click', function(){
  this.classList.add('controls--tab--active');
  orders_tab.classList.remove('controls--tab--active');
  all_items_tab.classList.remove('controls--tab--active');

  for(var i=0, count=items.length; i < count; i++){
    items[i].classList.replace('d-block', 'd-none');
  }

  for(var i=0, count=referrals.length; i < count; i++){
    referrals[i].classList.remove('d-none', 'd-block');
  }
});