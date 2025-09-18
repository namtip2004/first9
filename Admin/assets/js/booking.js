let selectedOptions = [];
let totalDuration = 0;
let selectedDate = null;
let allPromotions = [];
let latestPrice = 0;
let latestDiscount = 0;
let calculatedFinalPrice = 0;
let selectedPromotionId = 0;

function loadCustomers() {
  $.get("api/get_customers.php")
    .done(data => {
      console.log("Customers response:", data);
      $("#customer-select").empty();
      $("#customer-select").append(`<option value="" disabled selected>กรุณาเลือกลูกค้า</option>`);
      if (!Array.isArray(data) || data.length === 0) {
        $("#customer-select").append(`<option disabled>ไม่มีลูกค้า</option>`);
        return;
      }
      data.forEach(c => {
        $("#customer-select").append(`<option value="${c.customer_id}">${c.customer_name}</option>`);
      });
    })
    .fail(() => {
      console.error("Failed to load customers");
      $("#customer-select").html("<option disabled>ไม่สามารถโหลดรายชื่อลูกค้าได้</option>");
    });
}

function loadServices() {
  $.get("api/get_services_with_options.php")
    .done(data => {
      console.log("Services response:", data);
      $("#service-list").empty();
      if (!Array.isArray(data)) {
        $("#service-list").html("<p class='text-danger'>ไม่มีบริการหรือเกิดข้อผิดพลาดในการโหลด</p>");
        console.error("Invalid services data:", data);
        return;
      }
      if (data.length === 0) {
        $("#service-list").html("<p class='text-danger'>ไม่มีบริการให้เลือก</p>");
        return;
      }
      data.forEach(service => {
        let html = `
          <div class="col-md-6">
            <div class="card mb-2">
              <div class="card-header">${service.service_name}</div>
              <div class="card-body">
        `;
        if (Array.isArray(service.options)) {
          service.options.forEach(opt => {
            if (!opt.option_id || !opt.duration || !opt.price) {
              console.error("Invalid option:", opt);
              return;
            }
            html += `
              <div class="form-check">
                <input class="form-check-input service-opt" type="checkbox" 
                       data-id="${opt.option_id}" 
                       data-duration="${opt.duration}" 
                       data-price="${opt.price}"
                       data-service-id="${service.service_id}">
                <label class="form-check-label">${opt.duration} นาที (€${opt.price})</label>
              </div>
            `;
          });
        }
        html += `</div></div></div>`;
        $("#service-list").append(html);
      });
    })
    .fail((xhr, status, error) => {
      console.error("Failed to load services:", status, error);
      $("#service-list").html("<p class='text-danger'>ไม่สามารถโหลดบริการได้ กรุณาลองใหม่</p>");
    });
}

function loadPromotions() {
  $.get("api/get_promotion.php")
    .done(data => {
      console.log("Promotions response:", data);
      try {
        allPromotions = Array.isArray(data) ? data : [];
        $("#promotion-select").empty();
        $("#promotion-select").append(`<option value="0">ไม่มีโปรโมชัน</option>`);

        if (!Array.isArray(data) || data.length === 0) {
          console.warn("No valid promotions data received:", data);
          updatePriceAndDiscount();
          return;
        }

        const currentDate = new Date().toLocaleString("en-US", { timeZone: "Asia/Bangkok" });
        const currentDateObj = new Date(currentDate);
        currentDateObj.setHours(0, 0, 0, 0);
        console.log("Current date for comparison:", currentDateObj.toISOString().split('T')[0]);

        let validPromotions = 0;
        allPromotions.forEach(pm => {
          const startDate = new Date(pm.pm_start_date);
          const endDate = new Date(pm.pm_end_date);
          startDate.setHours(0, 0, 0, 0);
          endDate.setHours(23, 59, 59, 999);
          console.log(`Checking promotion ${pm.promotion_id}:`, {
            active: pm.active,
            startDate: pm.pm_start_date,
            endDate: pm.pm_end_date,
            startDateValid: !isNaN(startDate.getTime()),
            endDateValid: !isNaN(endDate.getTime()),
            isValid: pm.active == "1" && !isNaN(startDate.getTime()) && !isNaN(endDate.getTime()) && 
                     currentDateObj >= startDate && currentDateObj <= endDate
          });

          if (pm.active == "1" && !isNaN(startDate.getTime()) && !isNaN(endDate.getTime()) && 
              currentDateObj >= startDate && currentDateObj <= endDate) {
            const promotionName = `ส่วนลด ${pm.discount}% (ID: ${pm.promotion_id})${pm.apply_to_all == 1 ? ' - ทุกบริการ' : ''}`;
            $("#promotion-select").append(
              `<option value="${pm.promotion_id}" data-discount="${pm.discount}" data-apply-to-all="${pm.apply_to_all}" data-service-ids='${JSON.stringify(pm.service_ids || [])}'>${promotionName}</option>`
            );
            validPromotions++;
          }
        });

        console.log("Valid promotions found:", validPromotions);
        updatePriceAndDiscount();
      } catch (e) {
        console.error("Error parsing promotions:", e);
        allPromotions = [];
        updatePriceAndDiscount();
      }
    })
    .fail((xhr, status, error) => {
      console.error("Failed to load promotions:", status, error, "Response:", xhr.responseText);
      allPromotions = [];
      updatePriceAndDiscount();
    });
}

function updatePriceAndDiscount() {
  selectedOptions = [];
  totalDuration = 0;
  let price = 0;
  let selectedServiceIds = [];

  $(".service-opt:checked").each(function () {
    const optionPrice = parseFloat($(this).data("price"));
    if (isNaN(optionPrice)) return;
    selectedOptions.push(parseInt($(this).data("id")));
    totalDuration += parseInt($(this).data("duration"));
    price += optionPrice;
    selectedServiceIds.push(parseInt($(this).data("service-id")));
  });

  $("#total-duration").text(totalDuration);
  $("#total-price").text(`€${price.toFixed(2)}`);
  latestPrice = price;

  const selectedPromotion = $("#promotion-select").val();
  let discount = 0;
  
  if (selectedPromotion != "0") {
    const $selectedOption = $(`#promotion-select option[value="${selectedPromotion}"]`);
    const applyToAll = parseInt($selectedOption.data("apply-to-all"));
    const serviceIds = $selectedOption.data("service-ids") || [];
    discount = parseFloat($selectedOption.data("discount")) || 0;

    const validServiceIds = Array.isArray(serviceIds) ? serviceIds : [];
    
    const isApplicable = applyToAll == 1 || 
      (validServiceIds.length > 0 && validServiceIds.some(id => selectedServiceIds.includes(id)));
    
    if (!isApplicable) {
      discount = 0;
      $("#promotion-select").val("0");
      console.warn("Selected promotion is not applicable to chosen services");
    }
  }

  const discountAmount = (price * discount) / 100;
  const finalPrice = price - discountAmount;

  latestDiscount = discount;
  calculatedFinalPrice = finalPrice;
  selectedPromotionId = selectedPromotion != "0" ? parseInt(selectedPromotion) : null;

  $("#total-discount").text(`-€${discountAmount.toFixed(2)} (${discount}%)`);
  $("#final-price")
    .text(`€${finalPrice.toFixed(2)}`)
    .data("value", finalPrice.toFixed(2));

  console.log("Final price updated:", { 
    latestPrice, 
    discount, 
    calculatedFinalPrice, 
    selectedPromotionId,
    selectedServiceIds
  });
}

$(document).on("change", ".service-opt", function () {
  updatePriceAndDiscount();
  if (selectedDate) {
    $.post("api/get_available_times.php", {
      date: selectedDate,
      duration: totalDuration
    }, data => {
      $("#time-select").empty();
      if (!Array.isArray(data) || data.length === 0) {
        $("#time-select").append(`<option disabled>ไม่มีเวลาว่าง</option>`);
      } else {
        data.forEach(t => {
          $("#time-select").append(`<option value="${t}">${t}</option>`);
        });
      }
    }, 'json');
  }
});

$("#promotion-select").on("change", function() {
  updatePriceAndDiscount();
});

document.addEventListener("DOMContentLoaded", function () {
  const calendarEl = document.getElementById("calendar");
  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: "dayGridMonth",
    selectable: true,
    height: 500,
    dateClick: function (info) {
      selectedDate = info.dateStr;
      $(".fc-daygrid-day").removeClass("fc-day-selected");
      $(info.dayEl).addClass("fc-day-selected");

      if (totalDuration <= 0) {
        $("#time-select").html("<option disabled>กรุณาเลือกบริการก่อน</option>");
        return;
      }

      $.post("api/get_available_times.php", {
        date: selectedDate,
        duration: totalDuration
      }, data => {
        $("#time-select").empty();
        if (!Array.isArray(data) || data.length === 0) {
          $("#time-select").append(`<option disabled>ไม่มีเวลาว่าง</option>`);
        } else {
          data.forEach(t => {
            $("#time-select").append(`<option value="${t}">${t}</option>`);
          });
        }
      }, 'json');
    }
  });
  calendar.render();
});

$("#time-select").on("change", function () {
  const time_start = $(this).val();
  if (!selectedDate || !time_start || totalDuration <= 0) return;

  $.post("api/get_available_staff.php", {
    date: selectedDate,
    time: time_start,
    duration: totalDuration
  }, staff => {
    $("#staff-select").empty();
    if (!Array.isArray(staff) || staff.length === 0) {
      $("#staff-select").append(`<option disabled>ไม่มีพนักงานว่าง</option>`);
    } else {
      staff.forEach(s => {
        $("#staff-select").append(`<option value="${s.staff_id}">${s.staff_name}</option>`);
      });
    }
  }, 'json');
});

$("#book-btn").on("click", function (e) {
  e.preventDefault();

  const customer_id = $("#customer-select").val();
  const staff_id = $("#staff-select").val();
  let time_start = $("#time-select").val();
  const finalPrice = parseFloat(calculatedFinalPrice.toFixed(2));

  if (time_start && time_start.length === 5) {
    time_start += ":00";
  }

  if (!customer_id || !selectedDate || !time_start || !staff_id || selectedOptions.length === 0 || totalDuration <= 0 || isNaN(finalPrice)) {
    alert("กรุณาเลือก ลูกค้า, บริการ, วันที่, เวลา, พนักงาน และตรวจสอบว่าราคาถูกต้อง");
    return;
  }

  const payload = {
    customer_id: parseInt(customer_id),
    staff_id: parseInt(staff_id),
    date: selectedDate,
    time_start: time_start,
    duration: totalDuration,
    price: finalPrice,
    service_options: selectedOptions,
    promotion_id: selectedPromotionId
  };

  console.log("Sending payload:", payload);

  fetch("/first9/NiceAdmin/api/insert_booking.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload),
    signal: AbortSignal.timeout(10000)
  })
  .then(res => {
    return res.text().then(text => {
      if (!res.ok) throw new Error(`HTTP ${res.status}: ${text}`);
      return JSON.parse(text);
    });
  })
  .then(result => {
    if (result.success) {
      alert("จองสำเร็จ! หมายเลขจอง #" + result.booking_id);
      window.location.reload();
    } else {
      alert("เกิดข้อผิดพลาด: " + result.message);
    }
  })
  .catch(err => {
    console.error("Error:", err);
    try {
      const errObj = JSON.parse(err.message.split(": ")[1]);
      alert("เกิดข้อผิดพลาด: " + errObj.message);
    } catch (e) {
      alert("ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์: " + err.message);
    }
  });
});

loadCustomers();
loadServices();
loadPromotions();