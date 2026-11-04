// Show/hide password: works for every ".auth__password" block on the page
document.querySelectorAll('.auth__password').forEach(function (field) {
    const input = field.querySelector('input');
    const toggle = field.querySelector('.auth__toggle');

    if (!input || !toggle) {
        return;
    }

    toggle.addEventListener('click', function () {
        const isHidden = input.type === 'password';

        input.type = isHidden ? 'text' : 'password';
        toggle.textContent = isHidden ? 'Hide' : 'Show';
        toggle.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
    });
});


// ---------- Dashboard sidebar (mobile drawer) ----------
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('sidebar-overlay');
const menuButton = document.getElementById('sidebar-toggle');

// Open or close the mobile drawer
function setSidebar(open) {
    if (!sidebar || !overlay || !menuButton) {
        return;
    }

    sidebar.classList.toggle('-translate-x-full', !open);
    overlay.classList.toggle('hidden', !open);
    menuButton.setAttribute('aria-expanded', String(open));
}

if (menuButton) {
    menuButton.addEventListener('click', function () {
        const isOpen = menuButton.getAttribute('aria-expanded') === 'true';
        setSidebar(!isOpen);
    });
}

if (overlay) {
    overlay.addEventListener('click', function () {
        setSidebar(false);
    });
}

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        setSidebar(false);
    }
});


// ---------- Dashboard views: only one section is visible at a time ----------
const views = document.querySelectorAll('[data-view]');
const viewLinks = document.querySelectorAll('[data-view-link]');

const activeClasses = ['border-gold', 'bg-white/10', 'text-white'];
const inactiveClasses = ['border-transparent', 'text-white/70', 'hover:bg-white/5', 'hover:text-white'];

if (views.length > 0 && viewLinks.length > 0) {
    const defaultView = viewLinks[0].dataset.viewLink;

    // Which view does the address ask for? (for example "#users")
    function currentViewName() {
        const name = window.location.hash.replace('#', '');
        const exists = Array.from(views).some(function (view) {
            return view.dataset.view === name;
        });

        return exists ? name : defaultView;
    }

    function showView(name) {
        // Show the chosen section and hide the others
        views.forEach(function (view) {
            view.hidden = view.dataset.view !== name;
        });

        // Move the gold highlight in the sidebar
        viewLinks.forEach(function (link) {
            const isActive = link.dataset.viewLink === name;

            link.classList.remove(...activeClasses, ...inactiveClasses);
            link.classList.add(...(isActive ? activeClasses : inactiveClasses));

            if (isActive) {
                link.setAttribute('aria-current', 'page');
            } else {
                link.removeAttribute('aria-current');
            }
        });

        window.scrollTo(0, 0);
        setSidebar(false);   // on mobile, close the drawer after choosing
    }

    // Links change the address, and the address decides the view.
    // That makes Back, Forward and refresh work.
    window.addEventListener('hashchange', function () {
        showView(currentViewName());
    });

    showView(currentViewName());
}


// ---------- Users modal (admin dashboard) ----------
const usersModal = document.getElementById('users-modal');
const openModalButton = document.getElementById('open-users-modal');

if (usersModal && openModalButton) {
    // Open
    openModalButton.addEventListener('click', function () {
        usersModal.showModal();
    });

    // Close with any button marked data-close-modal (the X and Cancel)
    usersModal.querySelectorAll('[data-close-modal]').forEach(function (button) {
        button.addEventListener('click', function () {
            usersModal.close();
        });
    });

    // Close when the dark area outside the form is clicked
    usersModal.addEventListener('click', function (event) {
        if (event.target === usersModal) {
            usersModal.close();
        }
    });
}


// ---------- Users modal: submit the add-user form ----------
const addUserForm = document.getElementById('add-user-form');
const addUserMessage = document.getElementById('add-user-message');
let userWasAdded = false;

// Show a success or error message inside the modal
function showAddUserMessage(type, text, details) {
    addUserMessage.replaceChildren();

    const title = document.createElement('p');
    title.textContent = text;
    addUserMessage.appendChild(title);

    if (details && details.length > 0) {
        const list = document.createElement('ul');
        list.className = 'mt-2 list-disc space-y-1 pl-4';

        details.forEach(function (detail) {
            const item = document.createElement('li');
            item.textContent = detail;
            list.appendChild(item);
        });

        addUserMessage.appendChild(list);
    }

    addUserMessage.classList.remove(
        'hidden',
        'border-red-200', 'bg-red-50', 'text-red-700',
        'border-green-200', 'bg-green-50', 'text-green-800'
    );

    if (type === 'success') {
        addUserMessage.classList.add('border-green-200', 'bg-green-50', 'text-green-800');
    } else {
        addUserMessage.classList.add('border-red-200', 'bg-red-50', 'text-red-700');
    }
}

if (usersModal && addUserForm && addUserMessage) {

    addUserForm.addEventListener('submit', async function (event) {
        event.preventDefault();   // stop the browser from reloading the page

        const submitButton = addUserForm.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.textContent = 'Adding...';

        try {
            const response = await fetch(addUserForm.dataset.endpoint, {
                method: 'POST',
                body: new FormData(addUserForm),
            });
            const result = await response.json();

            if (result.success) {
                userWasAdded = true;
                showAddUserMessage('success', result.message);
                addUserForm.reset();
            } else {
                // Don't repeat the main message in the list underneath it
                const details = (result.errors || []).filter(function (error) {
                    return error !== result.message;
                });
                showAddUserMessage('error', result.message, details);
            }
        } catch (error) {
            showAddUserMessage('error', 'Something went wrong. Please try again.');
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = 'Add user';
        }
    });

    // When the modal closes: clear it, and refresh the page if a user was added
    usersModal.addEventListener('close', function () {
        addUserForm.reset();
        addUserMessage.classList.add('hidden');

        if (userWasAdded) {
            window.location.reload();
        }
    });
}


// ---------- Delete user (admin dashboard) ----------
const deleteModal = document.getElementById('delete-modal');
const deleteForm = document.getElementById('delete-user-form');
const deleteMessage = document.getElementById('delete-user-message');
const deleteUserId = document.getElementById('delete-user-id');
const deleteUserName = document.getElementById('delete-user-name');

if (deleteModal && deleteForm && deleteMessage) {

    // Each Delete button in the tables opens the confirmation with that user's details
    document.querySelectorAll('[data-delete-user]').forEach(function (button) {
        button.addEventListener('click', function () {
            deleteUserId.value = button.dataset.userId;
            deleteUserName.textContent = button.dataset.userName;
            deleteMessage.classList.add('hidden');
            deleteModal.showModal();
        });
    });

    // Cancel button and clicking outside close the confirmation
    deleteModal.querySelectorAll('[data-close-modal]').forEach(function (button) {
        button.addEventListener('click', function () {
            deleteModal.close();
        });
    });

    deleteModal.addEventListener('click', function (event) {
        if (event.target === deleteModal) {
            deleteModal.close();
        }
    });

    // Confirm: send the request
    deleteForm.addEventListener('submit', async function (event) {
        event.preventDefault();

        const submitButton = deleteForm.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.textContent = 'Deleting...';

        try {
            const response = await fetch(deleteForm.dataset.endpoint, {
                method: 'POST',
                body: new FormData(deleteForm),
            });
            const result = await response.json();

            if (result.success) {
                window.location.reload();   // the tables and the totals update
                return;
            }

            deleteMessage.textContent = result.message;
            deleteMessage.classList.remove('hidden');
        } catch (error) {
            deleteMessage.textContent = 'Something went wrong. Please try again.';
            deleteMessage.classList.remove('hidden');
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = 'Delete';
        }
    });
}