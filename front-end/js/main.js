document.addEventListener("DOMContentLoaded", function () {
    // Get references to the input field and the heading
    const bnameField = document.getElementById("post_title");
    const heading = document.querySelector(".main-banner-heading");

    // Add an input event listener to update the heading dynamically
    if (bnameField && heading) {
		bnameField.addEventListener("input", function () {
			const inputValue = bnameField.value.trim(); // Get the input value and trim whitespace
			heading.textContent = inputValue || "Business Name"; // Set heading text or default value
		});
	}
});

document.addEventListener('DOMContentLoaded', function () {
    const addButton = document.getElementById('add-social-media');
    const modal = document.getElementById('social-media-modal');
    const overlay = document.getElementById('modal-overlay');
    const saveButton = document.getElementById('save-social-media');
    const closeButton = document.getElementById('close-modal');
    const container = document.getElementById('social-icons-container');
    const modalTitle = document.getElementById('modal-title');

    if (!addButton || !modal || !overlay || !saveButton || !closeButton || !container || !modalTitle) {
        console.log("One or more elements are missing. Script execution stopped.");
        return; // Stop execution if any element is missing
    }

    let editingElement = null; // Keeps track of the element being edited

    // Open Modal for Adding or Editing
    const openModal = (element = null) => {
        modal.style.display = 'block';
        const typeField = document.getElementById('social-media-type');
        const linkField = document.getElementById('social-media-link');

        if (!typeField || !linkField) {
            console.warn("Modal input fields are missing.");
            return;
        }

        if (element) {
            const data = JSON.parse(element.dataset.info);
            typeField.value = data.type;
            linkField.value = data.link;
            modalTitle.textContent = 'Edit Social Media Link';
            editingElement = element;
        } else {
            typeField.value = 'facebook';
            linkField.value = '';
            modalTitle.textContent = 'Add Social Media Link';
            editingElement = null;
        }
    };

    const closeModal = () => {
        modal.style.display = 'none';
        editingElement = null;
    };

    const deleteIcon = (iconElement) => {
        if (iconElement) {
            iconElement.remove();
        }
    };

    closeButton.addEventListener('click', closeModal);
    overlay.addEventListener('click', closeModal);

    addButton.addEventListener('click', () => openModal());

    saveButton.addEventListener('click', () => {
        const typeField = document.getElementById('social-media-type');
        const linkField = document.getElementById('social-media-link');

        if (!typeField || !linkField) {
            console.warn("Modal input fields are missing.");
            return;
        }

        const type = typeField.value;
        const link = linkField.value.trim();

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
	document.querySelectorAll('.delete-icon').forEach(deleteIcon => {
	  deleteIcon.addEventListener('click', function () {
		// Find the parent div with class "social-icon-container"
		const socialIconContainer = this.closest('.social-icon-container');

		// Find the next sibling element (the hidden input field)
		const hiddenInput = socialIconContainer.nextElementSibling;

		// Remove both the social-icon-container and the hidden input field
		if (socialIconContainer && hiddenInput && hiddenInput.tagName === 'INPUT') {
		  socialIconContainer.remove();
		  hiddenInput.remove();
		}
	  });
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

});