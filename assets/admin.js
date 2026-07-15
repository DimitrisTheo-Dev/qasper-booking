(function () {
  'use strict';

  var form = document.querySelector('[data-qasper-settings-form]');
  if (!form) {
    return;
  }

  var customColorRadio = form.querySelector('#qasper-accent-mode-custom');
  var colorInput = form.querySelector('#qasper-accent');
  var submitButton = form.querySelector('button[type="submit"], input[type="submit"]');
  var savingLabel = form.getAttribute('data-saving-label') || '';
  var isDirty = false;
  var isSubmitting = false;

  function markDirty() {
    isDirty = true;
  }

  if (customColorRadio && colorInput) {
    colorInput.addEventListener('input', function () {
      customColorRadio.checked = true;
      markDirty();
    });
  }

  form.addEventListener('input', markDirty);
  form.addEventListener('change', markDirty);
  form.addEventListener('submit', function () {
    if (!form.checkValidity()) {
      return;
    }

    isSubmitting = true;
    if (submitButton) {
      submitButton.disabled = true;
      if (savingLabel && submitButton.tagName === 'INPUT') {
        submitButton.value = savingLabel;
      } else if (savingLabel) {
        submitButton.textContent = savingLabel;
      }
    }
  });

  window.addEventListener('beforeunload', function (event) {
    if (!isDirty || isSubmitting) {
      return;
    }

    event.preventDefault();
    event.returnValue = '';
  });
})();
