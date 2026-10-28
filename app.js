
const API_URL = 'api/events.php';

const form = document.getElementById('eventForm');
const eventId = document.getElementById('eventId');
const title = document.getElementById('title');
const date = document.getElementById('event_date');
const venue = document.getElementById('venue');
const description = document.getElementById('description');

const eventList = document.getElementById('eventList');
const loading = document.getElementById('loading');
const emptyState = document.getElementById('emptyState');
const notice = document.getElementById('notice');
const eventCount = document.getElementById('eventCount');
const connectionStatus = document.getElementById('connectionStatus');
const formHeading = document.getElementById('formHeading');
const submitButton = document.getElementById('submitButton');
const cancelEdit = document.getElementById('cancelEdit');
const searchInput = document.getElementById('searchInput');

let searchTimer;

// Display success or error messages.
function showNotice(message, type = 'success') {
    notice.textContent = message;
    notice.className = `notice ${type}`;
    notice.hidden = false;
}

function hideNotice() {
    notice.hidden = true;
}

// Safely display text received from the API.
function escapeHTML(value = '') {
    return String(value).replace(/[&<>"']/g, char => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
    })[char]);
}

// Common function for sending API requests.
async function request(url, options = {}) {
    const response = await fetch(url, options);

    let result;

    try {
        result = await response.json();
    } catch {
        throw new Error('Invalid JSON response from server.');
    }

    if (!response.ok || result.success === false) {
        throw new Error(
            result.message || `Request failed: ${response.status}`
        );
    }

    return result;
}

// READ and SEARCH events.
async function loadEvents(search = '') {
    loading.hidden = false;
    eventList.innerHTML = '';
    emptyState.hidden = true;

    try {
        const query = search.trim()
            ? `?search=${encodeURIComponent(search.trim())}`
            : '';

        const result = await request(API_URL + query);
        const events = result.data || [];

        connectionStatus.textContent = 'Connected';
        connectionStatus.style.color = '#168675';
        eventCount.textContent = events.length;

        eventList.innerHTML = events.map(item => {
            const eventDate = new Date(
                item.event_date + 'T00:00:00'
            );

            const day = eventDate.toLocaleDateString('en-IN', {
                day: '2-digit'
            });

            const month = eventDate.toLocaleDateString('en-IN', {
                month: 'short'
            });

            return `
                <article class="event-card">
                    <div class="date-tile">
                        <strong>${escapeHTML(day)}</strong>
                        <span>${escapeHTML(month)}</span>
                    </div>

                    <div class="event-info">
                        <h4>${escapeHTML(item.title)}</h4>

                        <p>
                            ${escapeHTML(item.venue)}
                            · ${escapeHTML(item.event_date)}
                        </p>

                        ${item.description
                            ? `<p>${escapeHTML(item.description)}</p>`
                            : ''}

                        <div class="event-actions">
                            <button
                                class="btn edit-btn"
                                data-action="edit"
                                data-id="${Number(item.id)}"
                                type="button">
                                Edit
                            </button>

                            <button
                                class="btn delete-btn"
                                data-action="delete"
                                data-id="${Number(item.id)}"
                                type="button">
                                Delete
                            </button>
                        </div>
                    </div>
                </article>
            `;
        }).join('');

        emptyState.hidden = events.length !== 0;

    } catch (error) {
        connectionStatus.textContent = 'Offline';
        connectionStatus.style.color = '#d9435f';

        eventCount.textContent = '—';
        showNotice(error.message, 'error');

    } finally {
        loading.hidden = true;
    }
}

// Reset the form after a successful operation.
function resetForm() {
    form.reset();
    eventId.value = '';

    formHeading.textContent = 'Add an event';
    submitButton.textContent = '+ Save event';
    cancelEdit.hidden = true;
}

// CREATE and UPDATE.
form.addEventListener('submit', async function(event) {
    event.preventDefault();
    hideNotice();

    if (!title.value.trim() ||
        !date.value ||
        !venue.value.trim()) {
        showNotice(
            'Please fill in the title, date and venue.',
            'error'
        );
        return;
    }

    const id = eventId.value;

    const data = {
        title: title.value.trim(),
        event_date: date.value,
        venue: venue.value.trim(),
        description: description.value.trim()
    };

    submitButton.disabled = true;
    submitButton.textContent = id ? 'Updating...' : 'Saving...';

    try {
        const result = await request(
            id ? `${API_URL}?id=${encodeURIComponent(id)}` : API_URL,
            {
                method: id ? 'PUT' : 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            }
        );

        resetForm();
        showNotice(result.message || 'Operation successful.');

        await loadEvents(searchInput.value);

    } catch (error) {
        showNotice(error.message, 'error');

    } finally {
        submitButton.disabled = false;
        submitButton.textContent = eventId.value
            ? 'Update event'
            : '+ Save event';
    }
});

// EDIT and DELETE buttons.
eventList.addEventListener('click', async function(event) {
    const button = event.target.closest('button[data-action]');

    if (!button) return;

    const id = Number(button.dataset.id);
    const action = button.dataset.action;

    // EDIT
    if (action === 'edit') {
        try {
            const result = await request(API_URL);

            const item = result.data.find(
                record => Number(record.id) === id
            );

            if (!item) {
                throw new Error('Event not found.');
            }

            eventId.value = item.id;
            title.value = item.title;
            date.value = item.event_date;
            venue.value = item.venue;
            description.value = item.description || '';

            formHeading.textContent = 'Edit event';
            submitButton.textContent = 'Update event';
            cancelEdit.hidden = false;

            hideNotice();
            title.focus();

            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });

        } catch (error) {
            showNotice(error.message, 'error');
        }
    }

    // DELETE
    if (action === 'delete') {
        if (!confirm('Are you sure you want to delete this event?')) {
            return;
        }

        button.disabled = true;
        button.textContent = 'Deleting...';

        try {
            const result = await request(
                `${API_URL}?id=${id}`,
                { method: 'DELETE' }
            );

            if (Number(eventId.value) === id) {
                resetForm();
            }

            showNotice(result.message);
            await loadEvents(searchInput.value);

        } catch (error) {
            showNotice(error.message, 'error');

            button.disabled = false;
            button.textContent = 'Delete';
        }
    }
});

// Cancel editing.
cancelEdit.addEventListener('click', function() {
    resetForm();
    hideNotice();
});

// Refresh the event list.
document.getElementById('refreshButton')
    .addEventListener('click', function() {
        loadEvents(searchInput.value);
    });

// Clear the search.
document.getElementById('clearSearch')
    .addEventListener('click', function() {
        searchInput.value = '';
        loadEvents();
    });

// Debounced search: wait 300 ms after typing stops.
searchInput.addEventListener('input', function() {
    clearTimeout(searchTimer);

    searchTimer = setTimeout(function() {
        loadEvents(searchInput.value);
    }, 300);
});

// Load events when the dashboard opens.
loadEvents();
