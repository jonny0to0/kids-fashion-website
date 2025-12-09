/**
 * Search JavaScript
 * Handles search autocomplete and suggestions
 */

let searchTimeout;

// Initialize search autocomplete
function initSearchAutocomplete() {
    const searchInput = document.querySelector('input[name="q"]');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length >= 2) {
                searchTimeout = setTimeout(() => {
                    fetchSearchSuggestions(query);
                }, 300);
            }
        });
    }
}

// Fetch search suggestions
async function fetchSearchSuggestions(query) {
    try {
        const siteUrl = window.location.origin + '/kid-bazar-ecom/public';
        const response = await fetch(`${siteUrl}/product/search-suggestions?q=${encodeURIComponent(query)}`);
        const data = await response.json();
        
        if (data.success && data.suggestions) {
            displaySearchSuggestions(data.suggestions);
        }
    } catch (error) {
        console.error('Error fetching search suggestions:', error);
    }
}

// Display search suggestions
function displaySearchSuggestions(suggestions) {
    // Implementation for displaying suggestions dropdown
    // This would typically show a dropdown menu below the search input
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initSearchAutocomplete();
});

