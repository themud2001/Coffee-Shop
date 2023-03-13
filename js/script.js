let navbar = document.querySelector('.navbar');
let timer = null;

let checkoutBtnId = document.querySelector("#checkout-btn");
let checkoutBtnClass = document.querySelector(".checkout-btn");

document.querySelector('#menu-btn').onclick = function() {
    navbar.classList.toggle('active');
    searchForm.classList.remove('active');
    cartItem.classList.remove('active');
}

let searchForm = document.querySelector('.search-form');

document.querySelector('#search-btn').onclick = function() {
    searchForm.classList.toggle('active');
    navbar.classList.remove('active');
    favItem.classList.remove('active');
    cartItem.classList.remove('active');

    if (historyItem) {
        historyItem.classList.remove('active');
    }
}

let cartItem = document.querySelector('.cart-items-container');
let favItem = document.querySelector('.fav-items-container');
let historyItem = document.querySelector('.history-items-container');

document.querySelector('#cart-btn').onclick = function() {
    cartItem.classList.toggle('active');
    favItem.classList.remove('active');

    if (historyItem) {
        historyItem.classList.remove('active');
    }

    navbar.classList.remove('active');
    searchForm.classList.remove('active');

    refreshCartItemsContainer();
}

let headerFavBtn = document.querySelector('#fav-btn');

if (headerFavBtn) {
    headerFavBtn.onclick = function() {
        favItem.classList.toggle('active');
        cartItem.classList.remove('active');
    
        if (historyItem) {
            historyItem.classList.remove('active');
        }
        
        navbar.classList.remove('active');
        searchForm.classList.remove('active');
    }
}

let historyBtn = document.querySelector('#history-btn');

if (historyBtn) {
    historyBtn.onclick = function() {
        historyItem.classList.toggle('active');
        cartItem.classList.remove('active');
        favItem.classList.remove('active');
        navbar.classList.remove('active');
        searchForm.classList.remove('active');
    }
}

window.onload = function() {
    let fav = JSON.parse(localStorage.getItem("fav"));
    let removeFromFavButtons = favItem.getElementsByClassName("remove-from-fav-btn");

    if (fav && removeFromFavButtons.length != 0) {
        for (let i = 0; i < fav.length; i++) {
            removeFromFavButtons[i].addEventListener("click", function() {
                removeFromFav(fav[i].productName);
            });
        }
    }

    refreshCartItemsContainer();
    checkActiveFavIcons();

    if (localStorage.getItem('checkoutTime')) {
        setTimeout(() => {
            playSound();
            alert('Your order has arrived!');
            localStorage.removeItem('checkoutTime')

            
        }, 0.1 * 60 * 1000);
    }
}

function addToCart(productName, price, image) {
    let cart = JSON.parse(localStorage.getItem("cart")) || [];

    for (let i = 0; i < cart.length; i++) {
        if (productName == cart[i].productName) {
            cart[i].count++;
            localStorage.setItem("cart", JSON.stringify(cart));
            return;
        }
    }

    let product = { productName: productName, price: price, image: image, count: 1 };

    cart.push(product);
    localStorage.setItem("cart", JSON.stringify(cart));
}

function removeFromCart(productName) {
    let cart = JSON.parse(localStorage.getItem("cart"));

    if (!cart || cart.length == 0) return;

    for (let i = 0; i < cart.length; i++) {
        if (productName == cart[i].productName) {
            if (cart[i].count > 1) {
                cart[i].count--;
                break;
            }
            
            cart.splice(i, 1);
        }
    }

    localStorage.setItem("cart", JSON.stringify(cart));
}

function refreshCartItemsContainer() {
    let cart = JSON.parse(localStorage.getItem("cart"));

    if (!cart || cart.length == 0) {
        cartItem.querySelector(".items").innerHTML = "<h1 style='height: 100%; text-align: center; opacity: 50%; margin-top: 50%'>Empty cart!</h1>";
        document.getElementById("cart-btn").innerText = "";

        if (checkoutBtnClass) {
            cartItem.querySelector(".checkout-btn").style.display="none";
        }
    } else {
        let cartItemsList = document.createElement("div");
        let total = 0;
        let totalNode = document.createElement("h1");
        totalNode.classList.add("cart-total");

        for (let i = 0; i < cart.length; i++) {
            let cartDiv = document.createElement("div");
            cartDiv.classList.add("cart-item");

            let cartSpan = document.createElement("span");
            cartSpan.classList.add("fas");
            cartSpan.classList.add("fa-times");
            cartSpan.classList.add("remove-from-cart-btn");

            let cartImage = document.createElement("img");
            cartImage.src = cart[i].image;
            cartImage.alt = cart[i].productName;

            let cartContent = document.createElement("div");
            cartContent.classList.add("content");

            let cartH3 = document.createElement("h3");
            cartH3.innerText = cart[i].productName;

            if (cart[i].count > 1) {
                cartH3.innerText = "(" + cart[i].count + ") " + cartH3.innerText;
            }

            let cartPrice = document.createElement("div");
            cartPrice.classList.add("price");
            cartPrice.innerText = "$" + cart[i].price;
            total += parseFloat(cart[i].price) * cart[i].count;

            cartContent.appendChild(cartH3);
            cartContent.appendChild(cartPrice);
            
            cartDiv.appendChild(cartSpan);
            cartDiv.appendChild(cartImage);
            cartDiv.appendChild(cartContent);

            cartItemsList.appendChild(cartDiv);
        }

        totalNode.innerHTML = "Your total is: <span>" + total.toFixed(2) + "$</span>";
        cartItemsList.appendChild(totalNode);

        cartItem.querySelector(".items").innerHTML = cartItemsList.innerHTML;

        let removeFromCartButtons = cartItem.getElementsByClassName("remove-from-cart-btn");

        for (let i = 0; i < removeFromCartButtons.length; i++) {
            removeFromCartButtons[i].addEventListener("click", function() {
                removeFromCart(cart[i].productName);
                refreshCartItemsContainer();
            });

        }

        if (checkoutBtnClass) {
            cartItem.querySelector(".checkout-btn").style.display="block";
        }

        document.getElementById("cart-btn").innerText = " (" + cart.length + ")";
    }
}

function checkActiveFavIcons() {
    let fav = JSON.parse(localStorage.getItem("fav"));
    let products = document.getElementById("products");
    let menu = document.getElementById("menu");

    if (!products || !menu) return;

    products = products.getElementsByClassName("box")
    menu = menu.getElementsByClassName("box")
    
    if (!fav || fav.length == 0) {
        for (let i = 0; i < products.length; i++) {
            let addToFavIcon = products[i].querySelector(".add-to-fav-icon");

            if (addToFavIcon) {
                addToFavIcon.classList.remove("active");
            }
        }

        for (let i = 0; i < menu.length; i++) {
            let addToFavIcon = menu[i].querySelector(".add-to-fav-icon");

            if (addToFavIcon) {
                addToFavIcon.classList.remove("active");
            }
        }
    } else {
        for (let i = 0; i < products.length; i++) {
            let productName = products[i].querySelector(".name").innerText;

            for (let j = 0; j < fav.length; j++) {
                if (productName == fav[j].productName) {
                    let addToFavIcon = products[i].querySelector(".add-to-fav-icon");

                    if (addToFavIcon) {
                        addToFavIcon.classList.add("active");
                    }
                    break;
                } else {
                    let addToFavIcon = products[i].querySelector(".add-to-fav-icon");

                    if (addToFavIcon) {
                        addToFavIcon.classList.remove("active");
                    }
                }
            }
        }

        for (let i = 0; i < menu.length; i++) {
            let menuName = menu[i].querySelector(".name").innerText;

            for (let j = 0; j < fav.length; j++) {
                if (menuName == fav[j].productName) {
                    let addToFavIcon = menu[i].querySelector(".add-to-fav-icon");

                    if (addToFavIcon) {
                        addToFavIcon.classList.add("active");
                    }
                    break;
                } else {
                    let addToFavIcon = menu[i].querySelector(".add-to-fav-icon");

                    if (addToFavIcon) {
                        addToFavIcon.classList.remove("active");
                    }
                }
            }
        }
    }
}

function addToFav(productName, price, image, parentElement) {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "add_to_fav.php");
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            let fav = JSON.parse(localStorage.getItem("fav")) || [];

            for (let i = 0; i < fav.length; i++) {
                if (productName == fav[i].productName) {
                    removeFromFav(productName);
                    return;
                }
            }

            let product = { productName: productName, price: price, image: image };

            fav.push(product);
            localStorage.setItem("fav", JSON.stringify(fav));
            appendFavToContainer(productName, price, image);

            let notification = document.querySelector(".notification-fav");
            notification.querySelector(".text").innerText = "Added to favorites!";

            if (!notification.classList.contains("active")) {
                notification.classList.add("active");
            }

            clearTimeout(timer);
            timer = setTimeout(function() {
                notification.classList.remove("active");
            }, 5000);

            checkActiveFavIcons();
        }
    }

    xhr.send("name=" + productName + "&price=" + price + "&image=" + image);
}

function removeFromFav(productName) {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "remove_from_fav.php");
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            let fav = JSON.parse(localStorage.getItem("fav"));         
            
            for (let i = 0; i < fav.length; i++) {
                if (productName == fav[i].productName) {
                    fav.splice(i, 1);
                    break;
                }
            }

            localStorage.setItem("fav", JSON.stringify(fav));
            removeFavFromContainer(productName);

            let notification = document.querySelector(".notification-fav");
            notification.querySelector(".text").innerText = "Removed from favorites!";

            if (!notification.classList.contains("active")) {
                notification.classList.add("active");
            }

            clearTimeout(timer);
            timer = setTimeout(function() {
                notification.classList.remove("active");
            }, 5000);

            checkActiveFavIcons();
        }
    };

    xhr.send("name=" + productName);
}

function appendFavToContainer(productName, price, image) {
    let fav = JSON.parse(localStorage.getItem("fav"));

    let favDiv = document.createElement("div");
    favDiv.classList.add("fav-item");

    let favAddToCart = document.createElement("span");
    favAddToCart.classList.add("fas");
    favAddToCart.classList.add("fa-shopping-cart");
    favAddToCart.classList.add("add-to-cart-icon-fav");

    let favSpan = document.createElement("span");
    favSpan.classList.add("fas");
    favSpan.classList.add("fa-times");
    favSpan.classList.add("remove-from-fav-btn");

    let favImage = document.createElement("img");
    favImage.src = image;
    favImage.alt = productName;

    let favContent = document.createElement("div");
    favContent.classList.add("content");

    let favH3 = document.createElement("h3");
    favH3.innerText = productName;

    let favPrice = document.createElement("div");
    favPrice.classList.add("price");
    favPrice.innerHTML = "<b>$</b>" + price + "<span></span>";

    favContent.appendChild(favH3);
    favContent.appendChild(favPrice);
    
    let addToCart = favDiv.appendChild(favAddToCart);
    let removeBtn = favDiv.appendChild(favSpan);
    favDiv.appendChild(favImage);
    favDiv.appendChild(favContent);

    addToCart.addEventListener("click", handleAddToCartIconInFav);
    removeBtn.addEventListener("click", function() {
        removeFromFav(productName);
    });

    if (fav.length > 1) {
        favItem.querySelector(".items").appendChild(favDiv);
    } else {
        favItem.querySelector(".items").innerHTML = "";
        favItem.querySelector(".items").appendChild(favDiv);
    }

    if (headerFavBtn) {
        headerFavBtn.innerText = " (" + fav.length + ")";
    }
}

function removeFavFromContainer(productName) {
    let fav = JSON.parse(localStorage.getItem("fav"));
    let favContainer = favItem.querySelector(".items");
    let favArray = favContainer.getElementsByClassName("fav-item")

    if (fav.length == 0) {
        favContainer.innerHTML = "<h1 style='height: 100%; text-align: center; opacity: 50%; margin-top: 50%'>Empty list!</h1>";
    } else {
        for (let i = 0; i < favArray.length; i++) {
            if (productName == favArray[i].querySelector("h3").innerText) {
                favArray[i].remove();
                break;
            }
        }
    }

    if (headerFavBtn) {
        if (fav.length > 0) {
            headerFavBtn.innerText = " (" + fav.length + ")";
        } else {
            headerFavBtn.innerText = "";
        }
    }
}

function handleAddToCart(parentElement) {
    let productName = parentElement.querySelector("h3").innerText;
    let price = parentElement.querySelector(".price").childNodes[1].nodeValue;
    let image = parentElement.querySelector("img").getAttribute("src");

    addToCart(productName, price, image);
    refreshCartItemsContainer();

    let notification = document.querySelector(".notification");
    
    if (!notification.classList.contains("active")) {
        notification.classList.add("active");
        setTimeout(function() {
            notification.classList.remove("active");
        }, 5000);
    }
}

function handleAddToCartIconInFav(e) {
    let parentElement = e.target.parentNode;
    handleAddToCart(parentElement);
}

function handleShoppingCartIcon(e) {
    let parentElement = e.target.parentNode.parentNode;
    handleAddToCart(parentElement);
}

function handleFavIcon(e) {
    let parentElement = e.target.parentNode.parentNode;
    let productName = parentElement.querySelector("h3").innerText;
    let price = parentElement.querySelector(".price").childNodes[1].nodeValue;
    let image = parentElement.querySelector("img").getAttribute("src");

    addToFav(productName, price, image, parentElement);
}

function handleCloseNotification(e) {
    let parentElement = e.target.parentNode;
    parentElement.classList.remove("active");
}

function handleCloseFavNotification(e) {
    let parentElement = e.target.parentNode;
    parentElement.classList.remove("active");
}

let addToCartIconsInFav = document.querySelectorAll(".add-to-cart-icon-fav");
let shoppingCartIcons = document.getElementsByClassName("add-to-cart-icon");
let favIcons = document.getElementsByClassName("add-to-fav-icon");

for (let i = 0; i < addToCartIconsInFav.length; i++) {
    addToCartIconsInFav[i].addEventListener("click", handleAddToCartIconInFav);
}

for (let i = 0; i < shoppingCartIcons.length; i++) {
    shoppingCartIcons[i].addEventListener("click", handleShoppingCartIcon);
}

for (let i = 0; i < favIcons.length; i++) {
    favIcons[i].addEventListener("click", handleFavIcon);
}

let closeNotificationButton = document.querySelector(".close-notification");

if (closeNotificationButton) {
    closeNotificationButton.addEventListener("click", handleCloseNotification);
}

let closeFavNotificationButton = document.querySelector(".close-fav-notification");

if (closeFavNotificationButton) {
    closeFavNotificationButton.addEventListener("click", handleCloseFavNotification);
}

function playSound() {
    let audio=document.querySelector("#notifSound")
    audio.play()
}
    
function handleCheckout() {
    let total = document.querySelector(".cart-total span").innerText;
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "checkout.php");
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            playSound();
            alert('Your order will arrive in around 30 minutes!');
            
            localStorage.removeItem('cart');
            localStorage.setItem('checkoutTime', true);
        }
    };

    xhr.send("total=" + total);
}

if (checkoutBtnId) {
    document.querySelector("#checkout-btn").addEventListener("click", handleCheckout);
}