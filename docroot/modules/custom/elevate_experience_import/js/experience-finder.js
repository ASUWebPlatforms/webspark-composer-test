/**
 * @file
 * Experience Finder: turns the BEF checkbox fieldsets into labelled dropdowns
 * and wires the hero collapse toggle.
 */
((Drupal, once) => {
  'use strict';

  /**
   * Builds a single dropdown out of a BEF checkbox fieldset.
   */
  function buildDropdown(fieldset) {
    const legend = fieldset.querySelector(':scope > legend');
    const labelText = legend ? legend.textContent.trim() : '';
    const selector = fieldset.getAttribute('data-drupal-selector') || '';
    let placeholder = Drupal.t('All');
    if (selector.indexOf('program') !== -1) {
      placeholder = Drupal.t('Show all');
    }
    else if (selector.indexOf('location') !== -1) {
      placeholder = Drupal.t('Anywhere');
    }

    // Cell wrapper with the field label on top.
    const field = document.createElement('div');
    field.className = 'ef-field ef-field--dropdown';
    const label = document.createElement('span');
    label.className = 'ef-field__label';
    label.textContent = labelText;
    fieldset.parentNode.insertBefore(field, fieldset);
    field.appendChild(label);
    field.appendChild(fieldset);

    fieldset.classList.add('ef-dropdown');
    if (legend) {
      legend.classList.add('visually-hidden');
    }

    // Toggle button.
    const toggle = document.createElement('button');
    toggle.type = 'button';
    toggle.className = 'ef-dropdown__toggle';
    toggle.setAttribute('aria-expanded', 'false');
    toggle.innerHTML =
      '<span class="ef-dropdown__value"></span><span class="ef-dropdown__chevron" aria-hidden="true"></span>';

    // Panel: move the checkbox items into it.
    const panel = document.createElement('div');
    panel.className = 'ef-dropdown__panel';
    Array.from(fieldset.children).forEach((child) => {
      if (child !== legend) {
        panel.appendChild(child);
      }
    });
    fieldset.appendChild(toggle);
    fieldset.appendChild(panel);

    const valueEl = toggle.querySelector('.ef-dropdown__value');
    const update = () => {
      const checked = panel.querySelectorAll('input[type="checkbox"]:checked, input[type="radio"]:checked');
      valueEl.textContent = checked.length
        ? Drupal.formatPlural(checked.length, '1 selected', '@count selected')
        : placeholder;
    };
    update();

    toggle.addEventListener('click', (e) => {
      e.preventDefault();
      const open = fieldset.classList.toggle('is-open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    panel.addEventListener('change', update);
  }

  let csrfTokenPromise = null;

  /**
   * Fetches (and caches) a CSRF token for authenticated POST requests.
   */
  function getCsrfToken() {
    if (!csrfTokenPromise) {
      csrfTokenPromise = fetch(Drupal.url('session/token'), { credentials: 'same-origin' })
        .then((r) => r.text());
    }
    return csrfTokenPromise;
  }

  /**
   * Shows a small "log in" tooltip next to a favourite button.
   */
  function showLoginTooltip(button, settings) {
    document.querySelectorAll('.experience-card__fav-tooltip').forEach((t) => t.remove());
    const tip = document.createElement('span');
    tip.className = 'experience-card__fav-tooltip';
    tip.setAttribute('role', 'status');
    const link = document.createElement('a');
    link.href = settings.loginUrl;
    link.textContent = Drupal.t('Log in');
    tip.appendChild(document.createTextNode(Drupal.t('Please ') ));
    tip.appendChild(link);
    tip.appendChild(document.createTextNode(' ' + Drupal.t('to save favorites.')));
    button.parentNode.appendChild(tip);
    const remove = (e) => {
      if (!tip.contains(e.target) && e.target !== button) {
        tip.remove();
        document.removeEventListener('click', remove);
      }
    };
    setTimeout(() => document.addEventListener('click', remove));
  }

  /**
   * Reflects flagged/unflagged state on a heart button.
   */
  function setFavState(button, flagged) {
    button.setAttribute('aria-pressed', flagged ? 'true' : 'false');
    button.setAttribute(
      'aria-label',
      flagged ? Drupal.t('Remove from favorites') : Drupal.t('Add to favorites'),
    );
  }

  Drupal.behaviors.experienceFinderFavorites = {
    attach(context) {
      const settings = (drupalSettings && drupalSettings.experienceFinder) || {};

      // Mark the cards the current user has already favourited.
      if (settings.authenticated && settings.listUrl) {
        fetch(settings.listUrl, { credentials: 'same-origin', headers: { Accept: 'application/json' } })
          .then((r) => (r.ok ? r.json() : { nids: [] }))
          .then((data) => {
            const flagged = new Set((data.nids || []).map(String));
            document.querySelectorAll('[data-experience-fav]').forEach((btn) => {
              if (flagged.has(String(btn.dataset.nid))) {
                setFavState(btn, true);
              }
            });
          })
          .catch(() => {});
      }

      // "Favorites" toggle: filter the listing to the user's favourites.
      once('ef-fav-toggle', '[data-experience-favorites-toggle]', context).forEach((button) => {
        const url = new URL(window.location.href);
        const active = url.searchParams.get('favorites') === '1';
        button.setAttribute('aria-pressed', active ? 'true' : 'false');
        button.classList.toggle('is-active', active);

        button.addEventListener('click', (e) => {
          e.preventDefault();
          if (!settings.authenticated) {
            showLoginTooltip(button, settings);
            return;
          }
          const next = new URL(window.location.href);
          if (active) {
            next.searchParams.delete('favorites');
          }
          else {
            next.searchParams.set('favorites', '1');
          }
          next.searchParams.delete('page');
          window.location.assign(next.toString());
        });
      });

      once('ef-fav', '[data-experience-fav]', context).forEach((button) => {
        button.addEventListener('click', (e) => {
          e.preventDefault();
          e.stopPropagation();

          if (!settings.authenticated) {
            showLoginTooltip(button, settings);
            return;
          }
          if (button.dataset.busy === '1') {
            return;
          }
          button.dataset.busy = '1';

          const url = (settings.toggleUrlBase || '').replace(/\/0\/toggle$/, '/' + button.dataset.nid + '/toggle');
          getCsrfToken()
            .then((token) =>
              fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'X-CSRF-Token': token, Accept: 'application/json' },
              }),
            )
            .then((r) => (r.ok ? r.json() : Promise.reject(r)))
            .then((data) => setFavState(button, !!data.flagged))
            .catch(() => {})
            .finally(() => {
              button.dataset.busy = '';
            });
        });
      });
    },
  };

  Drupal.behaviors.experienceFinderDropdowns = {
    attach(context) {
      once('ef-dropdown', '.experience-finder__filters fieldset.fieldgroup', context)
        .forEach(buildDropdown);

      // Close any open dropdown when clicking outside of it.
      once('ef-outside', 'body', context).forEach((body) => {
        body.addEventListener('click', (e) => {
          document.querySelectorAll('.ef-dropdown.is-open').forEach((dd) => {
            if (!dd.contains(e.target)) {
              dd.classList.remove('is-open');
              const t = dd.querySelector('.ef-dropdown__toggle');
              if (t) {
                t.setAttribute('aria-expanded', 'false');
              }
            }
          });
        });
      });

      // Hero collapse toggle (smoothly animated via max-height).
      once('ef-collapse', '[data-ef-collapse]', context).forEach((btn) => {
        const finder = btn.closest('.experience-finder');
        const filters = finder && finder.querySelector('.experience-finder__filters');
        if (!filters) {
          return;
        }
        btn.addEventListener('click', () => {
          const collapsing = !finder.classList.contains('experience-finder--collapsed');
          // Animate from the element's real height so the easing is accurate.
          filters.style.overflow = 'hidden';
          filters.style.maxHeight = filters.scrollHeight + 'px';

          if (collapsing) {
            // Force a reflow so the starting height is applied before collapsing.
            void filters.offsetHeight;
            finder.classList.add('experience-finder--collapsed');
            filters.style.maxHeight = '0px';
            btn.setAttribute('aria-expanded', 'false');
          }
          else {
            finder.classList.remove('experience-finder--collapsed');
            filters.style.maxHeight = filters.scrollHeight + 'px';
            btn.setAttribute('aria-expanded', 'true');
            // Once expanded, drop the inline limits so dropdown panels can
            // overflow the filters area normally.
            const done = (e) => {
              if (e.propertyName !== 'max-height') {
                return;
              }
              filters.style.maxHeight = '';
              filters.style.overflow = '';
              filters.removeEventListener('transitionend', done);
            };
            filters.addEventListener('transitionend', done);
          }
        });
      });
    },
  };
})(Drupal, once);
