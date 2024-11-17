jQuery(document).ready(function($){
    const $form = $('#dbw-cost-calc-form'),
        $quoteBtn = $('#get-quote-btn'),
        $thankYouCloseBtn = $('#thank-you-close'),
        $termsToggle = $('.dbw-cost-calc-terms-title');

    $quoteBtn.on('click', function(e) {
        e.preventDefault();
        $form.attr('data-step', 'quote-submit');
        return false;
    });

    $form.on('submit', function(e) {
        e.preventDefault();

        $form.attr('data-step', 'thank-you');
        return false;
    });

    $thankYouCloseBtn.on('click', function(e) {
        e.preventDefault();
        // todo reset calc
        // todo empty fields
        $form.attr('data-step', 'get-quote');
        return false;
    });

    $termsToggle.on('click', function() {
        const $_this = $(this),
            $svg = $_this.find('svg');
            $content = $_this.parent().find('.dbw-cost-calc-terms-content');

        if ($_this.is('.active')) {
            $svg.removeClass('active');
            $content.slideUp(function() {
                $_this.removeClass('active');
            });
        } else {
            $svg.addClass('active');
            $_this.addClass('active');
            $content.slideDown();
        }
    });
});



const instancePricing = {
    "oracle": 600,
    "msSQL": 600,
    "mysql": 425,
    "postgres": 425,
    "mariaDB": 350,
    "sybase": 350
};

const addOns = [
    { name: "SQL Performance package", price: 120, platforms: ["Oracle", "MSSQL", "MySQL", "PostgreSQL", "MariaDB"] },
    { name: "Maintenance package", price: 120, platforms: ["MSSQL"] },
    { name: "Security and Compliance package", price: 120, platforms: ["MSSQL"] },
    { name: "Cloud Router", price: 5000, platforms: ["ControlCenter"] }
];

const discountRates = [
    { minQty: 10, discount: 0.1 },
    { minQty: 25, discount: 0.15 },
    { minQty: 50, discount: 0.23 },
    { minQty: 100, discount: 0.33 },
    { minQty: 200, discount: 0.45 },
    { minQty: 300, discount: 0.55 },
    { minQty: 400, discount: 0.59 },
    { minQty: 500, discount: 0.62 },
    { minQty: 600, discount: 0.64 },
    { minQty: 700, discount: 0.67 },
    { minQty: 800, discount: 0.68 },
    { minQty: 900, discount: 0.70 },
    { minQty: 1000, discount: 0.72 },
    { minQty: 1100, discount: 0.74 },
    { minQty: 1200, discount: 0.75 },
    { minQty: 1300, discount: 0.76 },
    { minQty: 1400, discount: 0.77},
    { minQty: 1500, discount: 0.78 },
    { minQty: 2000, discount: 0.79 },
    { minQty: 2500, discount: 0.80 },
    { minQty: 3000, discount: 0.81 }
];

function populateAddOns() {
    const container = document.getElementById('addonsContainer');
    addOns.forEach((addon, index) => {
        const div = document.createElement('div');
        div.classList.add('form-group');
        const label = document.createElement('label');
        label.innerText = `${addon.name} ($${addon.price}) for ${addon.platforms.join(", ")}:`;
        label.setAttribute('for', `addon${index}`);
        const input = document.createElement('input');
        input.type = 'number';
        input.id = `addon${index}`;
        input.name = `addon${index}`;
        input.value = 0;
        input.min = 0;
        div.appendChild(label);
        div.appendChild(input);
        container.appendChild(div);
    });
}

function calculatePrice() {
    const quantities = {
        oracle: document.getElementById('oracle').value,
        msSQL: document.getElementById('msSQL').value,
        mysql: document.getElementById('mysql').value,
        postgres: document.getElementById('postgres').value,
        mariaDB: document.getElementById('mariaDB').value,
        sybase: document.getElementById('sybase').value
    };

    let priceBeforeDiscount = 0;
    let totalInstanceQuantity = 0;

    for (const [key, value] of Object.entries(quantities)) {
        priceBeforeDiscount += value * instancePricing[key];
        totalInstanceQuantity += parseInt(value);
    }

    addOns.forEach((addon, index) => {
        const value = document.getElementById(`addon${index}`).value;
        priceBeforeDiscount += value * addon.price;
    });

    let discount = 0;
    for (const rate of discountRates) {
        if (totalInstanceQuantity >= rate.minQty) {
            discount = rate.discount;
        }
    }

    const discountAmount = priceBeforeDiscount * discount;
    const priceAfterDiscount = priceBeforeDiscount - discountAmount;

    const totalPricePerInstance = priceAfterDiscount / totalInstanceQuantity;
    const totalPricePerMonth = priceAfterDiscount / 12;

    document.getElementById('priceBeforeDiscount').innerText = priceBeforeDiscount.toFixed(2);
    document.getElementById('discountAmount').innerText = discountAmount.toFixed(2);
    document.getElementById('totalPrice').innerText = priceAfterDiscount.toFixed(2);
    document.getElementById('totalPricePerInstance').innerText = totalPricePerInstance.toFixed(2);
    document.getElementById('totalPricePerMonth').innerText = totalPricePerMonth.toFixed(2);
}

function sendQuote() {
    const quantities = {
        oracle: document.getElementById('oracle').value,
        msSQL: document.getElementById('msSQL').value,
        mysql: document.getElementById('mysql').value,
        postgres: document.getElementById('postgres').value,
        mariaDB: document.getElementById('mariaDB').value,
        sybase: document.getElementById('sybase').value
    };

    const addons = addOns.map((addon, index) => {
        return { name: addon.name, quantity: document.getElementById(`addon${index}`).value };
    });

    const totalPrice = document.getElementById('totalPrice').innerText;
    const priceBeforeDiscount = document.getElementById('priceBeforeDiscount').innerText;
    const discountAmount = document.getElementById('discountAmount').innerText;
    const totalPricePerInstance = document.getElementById('totalPricePerInstance').innerText;
    const totalPricePerMonth = document.getElementById('totalPricePerMonth').innerText;

    const quoteData = {
        instances: quantities,
        addons: addons,
        totalPrice: parseFloat(totalPrice),
        priceBeforeDiscount: parseFloat(priceBeforeDiscount),
        discountAmount: parseFloat(discountAmount),
        totalPricePerInstance: parseFloat(totalPricePerInstance),
        totalPricePerMonth: parseFloat(totalPricePerMonth)
    };

    const jsonString = JSON.stringify(quoteData);
    console.log(jsonString); // This would be sent to the server

    alert("Quote request sent! Check console for JSON data.");
}

window.onload = populateAddOns;