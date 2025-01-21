document.addEventListener("DOMContentLoaded", function () {
    /*const selectBox = document.querySelector(".custom-selectbox");
    const inputField = document.querySelector("#business-category");
    const dropdown = document.querySelector(".select-options");

    // Toggle dropdown
    inputField.addEventListener("focus", () => {
        selectBox.classList.add("open");
    });

    inputField.addEventListener("input", function () {
        const filter = inputField.value.toLowerCase();
        const options = dropdown.querySelectorAll("li");

        options.forEach(option => {
            const text = option.innerText.toLowerCase();
            if (text.includes(filter)) {
                option.style.display = "flex";
            } else {
                option.style.display = "none";
            }
        });
    });

    dropdown.addEventListener("click", (e) => {
        const clickedItem = e.target.closest("li"); // Check if an <li> is clicked
        if (clickedItem) {
            const selectedText = clickedItem.innerText.trim(); // Get the text content of the <li>
            const selectedCid = clickedItem.getAttribute("data-cid"); // Get the data-cid attribute
    
            if (selectedCid) {
                // Set values in the respective input fields
                document.getElementById("gd_placecategory").value = selectedCid;
                document.getElementById("default_category").value = selectedCid;
            }
    
            inputField.value = selectedText; // Update the input field's value with the text
            selectBox.classList.remove("open"); // Close the dropdown
        }
    });

    // Close dropdown on blur
    inputField.addEventListener("blur", () => {
        setTimeout(() => selectBox.classList.remove("open"), 200);
    });*/

    /*document.getElementById("banner-upload-section").addEventListener("click", function () {
        const fileInput = document.getElementById("file-upload");
        fileInput.click(); // Trigger file selection
    });

    document.getElementById("file-upload").addEventListener("change", function (event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                document.querySelector(".banner-section").style.backgroundImage = `url(${e.target.result})`;
            };
            reader.readAsDataURL(file);
        }
        let fileInput = jQuery(this)[0].files[0]; // Get the selected file

        if (!fileInput) {
            alert('Please select a file.');
            return;
        }

        let fileData = new FormData();
        fileData.append('file', fileInput);
        fileData.append('action', 'sw_file_upload');
        fileData.append('security', ajax_object.nonce);

        // Send the file via AJAX
        jQuery.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: fileData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    jQuery('#post_images').html(
                        `${response.data.url}|${response.data.attachment_id}||`
                    );
                } else {
                    jQuery('#post_images').html('');
                }
            },
            error: function () {
                jQuery('#post_images').html('');
            },
        });
    });*/

    // Get references to the input field and the heading
    const bnameField = document.getElementById("post_title");
    const heading = document.querySelector(".main-banner-heading");

    // Add an input event listener to update the heading dynamically
    bnameField.addEventListener("input", function () {
        const inputValue = bnameField.value.trim(); // Get the input value and trim whitespace
        heading.textContent = inputValue || "Business Name"; // Set heading text or default value
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const addButton = document.getElementById('add-social-media');
    const modal = document.getElementById('social-media-modal');
    const overlay = document.getElementById('modal-overlay');
    const saveButton = document.getElementById('save-social-media');
    const closeButton = document.getElementById('close-modal');
    const container = document.getElementById('social-icons-container');
    const modalTitle = document.getElementById('modal-title');

    let editingElement = null; // Keeps track of the element being edited

    // Open Modal for Adding or Editing
    const openModal = (element = null) => {
        modal.style.display = 'block';
        if (element) {
            const data = JSON.parse(element.dataset.info);
            document.getElementById('social-media-type').value = data.type;
            document.getElementById('social-media-link').value = data.link;
            modalTitle.textContent = 'Edit Social Media Link';
            editingElement = element;
        } else {
            document.getElementById('social-media-type').value = 'facebook';
            document.getElementById('social-media-link').value = '';
            modalTitle.textContent = 'Add Social Media Link';
            editingElement = null;
        }
    };

    const closeModal = () => {
        modal.style.display = 'none';
        editingElement = null;
    };

    const deleteIcon = (iconElement) => {
        iconElement.remove();
    };

    closeButton.addEventListener('click', closeModal);
    overlay.addEventListener('click', closeModal);

    addButton.addEventListener('click', () => openModal());

    saveButton.addEventListener('click', () => {
        const type = document.getElementById('social-media-type').value;
        const link = document.getElementById('social-media-link').value;

        if (link) {
            if (editingElement) {
                editingElement.dataset.info = JSON.stringify({ type, link });
                editingElement.querySelector('i').className = `fab fa-${type}`;
            } else {
                const containerElement = document.createElement('div');
                containerElement.classList.add('social-icon-container');

                const icon = document.createElement('div');
                icon.className = `social-icon ${type}`;
                icon.dataset.info = JSON.stringify({ type, link });
                icon.innerHTML = `<i class="fab fa-${type}"></i>`;
                containerElement.appendChild(icon);

                const deleteBtn = document.createElement('div');
                deleteBtn.className = 'delete-icon';
                deleteBtn.innerHTML = '&times;';
                deleteBtn.addEventListener('click', () => deleteIcon(containerElement));
                containerElement.appendChild(deleteBtn);

                container.appendChild(containerElement);

                icon.title = link;

                // Save to hidden input field
                const hiddenField = document.createElement('input');
                hiddenField.type = 'hidden';
                hiddenField.name = 'social_media[]';
                hiddenField.value = JSON.stringify({ type, link });

                container.appendChild(hiddenField);
            }

            closeModal();
        } else {
            alert('Please enter a valid link.');
        }
    });
});

jQuery(document).ready(function($) {
    // Get length of desired class
    let post_images = $(".post-attachment-img").length;

    // Slick
    if( post_images >= 3 ) {
        $(".post-banner-slider").slick({
            infinite: true,
            slidesToShow: 3,
            slidesToScroll: 1,
            cssEase: "linear",
            prevArrow: '<button type="button" class="slick-prev"><i class="fa fa-chevron-left"></i></button>',
            nextArrow: '<button type="button" class="slick-next"><i class="fa fa-chevron-right"></i></button>',
        });
    } else if ( post_images == 2 ){
        $(".post-banner-slider").slick({
            infinite: true,
            slidesToShow: 2,
            slidesToScroll: 1,
            cssEase: "linear",
            prevArrow: '<button type="button" class="slick-prev"><i class="fa fa-chevron-left"></i></button>',
            nextArrow: '<button type="button" class="slick-next"><i class="fa fa-chevron-right"></i></button>',
        });
    }
    
    // Remove empty paragraphs from the content area
    $("p").each(function(){
        let $this = $(this);

        if( ! $this.html().length ) {
            // $this.remove();
            $this.css("display", "none");
        }
    });

    //ajax callback
    jQuery( document ).ajaxComplete(function( event, xhr, settings ) {

        // console.log(settings.data);
        if( settings.data.indexOf("geodir_save_post") > -1 ) {
            
        }

    });
});