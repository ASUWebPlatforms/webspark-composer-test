(function ($, Drupal, drupalSettings) {
  'use strict';

  if (typeof $ === 'undefined' || typeof Drupal === 'undefined') {
    return;
  }

  const API = {
    csrfToken: '/api/sp-learningmod/csrf-token',
    budget: '/api/sp-learningmod/budget',
    visitedNodes: '/api/sp-learningmod/visited-nodes',
    trackVisit: '/api/sp-learningmod/track-visit',
    warningState: '/api/sp-learningmod/warning-state',
    setWarning: '/api/sp-learningmod/set-warning',
    resetProgress: '/api/sp-learningmod/reset',
    selectedResponses: '/api/sp-learningmod/selected-responses',
    councilProgress: '/api/sp-learningmod/council-progress'
  };

  let csrfToken = null;
  let initialized = false;

  function cacheBuster() {
    return '?_=' + new Date().getTime();
  }

  async function fetchApi(endpoint, options = {}) {
    const defaultOptions = {
      method: 'GET',
      credentials: 'same-origin',
      headers: {
        'Accept': 'application/json',
        'Cache-Control': 'no-cache',
        'X-Requested-With': 'XMLHttpRequest'
      }
    };

    const url = endpoint + cacheBuster();

    try {
      const response = await fetch(url, { ...defaultOptions, ...options });
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      const data = await response.json();
      return data;
    } catch (error) {
      return null;
    }
  }

  async function postApi(endpoint, data = {}) {
    if (!csrfToken) {
      const tokenResponse = await fetchApi(API.budget);
      if (tokenResponse && tokenResponse.csrfToken) {
        csrfToken = tokenResponse.csrfToken;
      }
    }

    const params = new URLSearchParams(data);
    if (csrfToken) {
      params.append('csrf_token', csrfToken);
    }

    const url = endpoint + '?' + params.toString() + '&_=' + new Date().getTime();

    try {
      const response = await fetch(url, {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
          'Accept': 'application/json',
          'Cache-Control': 'no-cache',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-Token': csrfToken || ''
        }
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const responseData = await response.json();
      return responseData;
    } catch (error) {
      return null;
    }
  }

  function updateBudgetBanner(data) {
    const $banner = $('#budget-banner');
    const $progressBar = $banner.find('.sp-budget-bar');

    if (!$banner.length || !$progressBar.length) {
      return;
    }

    const percentage = data.percentage || 0;

    if (typeof Drupal.spAnimateBudgetBar === 'function') {
      Drupal.spAnimateBudgetBar($progressBar, percentage);
    } else {
      $progressBar.find('.loading-indicator').remove();

      $progressBar.removeClass('green orange red');
      if (percentage > 100) {
        $progressBar.addClass('red');
      } else if (percentage >= 80) {
        $progressBar.addClass('orange');
      } else {
        $progressBar.addClass('green');
      }

      $progressBar.css('width', Math.min(percentage, 100) + '%');
      $progressBar.text(percentage > 0 ? percentage + '%' : '');
    }
  }

  async function checkAndHandleWarnings(budgetData) {
    if (!budgetData || !budgetData.authenticated) {
      return;
    }

    const action = budgetData.action;

    if (!action || action === 'none' || action === 'already_visited') {
      return;
    }

    const warningState = await fetchApi(API.warningState);

    if (!warningState) {
      return;
    }

    if (action === 'fired') {
      window.location.href = '/learning/prostitution/you-are-fired';
      return;
    }

    if (action === 'final_warning' && !warningState.final_warning_shown) {
      await postApi(API.setWarning, { type: 'final_warning' });
      window.location.href = '/learning/prostitution/final-warning';
      return;
    }

    if (action === 'warning' && !warningState.warning_shown) {
      await postApi(API.setWarning, { type: 'warning' });
      window.location.href = '/learning/prostitution/warning';
      return;
    }
  }

  async function loadVisitedNodes() {
    let $container = $('#visited-nodes-container');
    if (!$container.length) {
      $container = $('.visited-nodes-ajax-container');
    }

    if (!$container.length) {
      return;
    }

    $container.html('<div class="loading">Loading...</div>');

    const data = await fetchApi(API.visitedNodes);

    if (!data || !data.authenticated) {
      $container.html('<p>Please log in to see your visited items.</p>');
      return;
    }

    if (!data.items || data.items.length === 0) {
      $container.html('<p>Your items consulted during the analysis stage will be listed here.</p>');
      return;
    }

    let html = '<div class="people-grid">';

    data.items.forEach(item => {
      html += `
        <div class="person-card">
          ${item.image ? `
            <div class="person-image">
              <img src="${item.image}" alt="${item.title}">
            </div>
          ` : ''}
          <div class="person-details">
            <h3 class="person-title"><a href="${item.link}">${item.title}</a></h3>
            ${item.description ? `<p class="person-description">${item.description}</p>` : ''}
          </div>
        </div>
      `;
    });

    html += '</div>';
    $container.html(html);
  }

  async function trackCurrentNodeVisit() {
    const nid = drupalSettings?.sp_learningmod?.currentNodeId ||
      $('[data-sp-track-nid]').attr('data-sp-track-nid');

    if (!nid || nid === '' || nid === '0') {
      return null;
    }

    const result = await postApi(API.trackVisit, { nid: nid });

    if (result && result.success) {
      updateBudgetBanner(result);
      await checkAndHandleWarnings(result);
    }

    return result;
  }

  async function loadBudgetState() {
    const data = await fetchApi(API.budget);

    if (data && data.authenticated) {
      updateBudgetBanner(data);
      if (data.csrfToken) {
        csrfToken = data.csrfToken;
      }
    }

    return data;
  }

  async function loadSelectedResponses() {
    const $container = $('#selected-responses-container');

    if (!$container.length) {
      return;
    }

    const data = await fetchApi(API.selectedResponses);

    if (!data || !data.authenticated || !data.responses.length) {
      return;
    }

    let html = '<ul class="selected-responses-list">';
    data.responses.forEach(response => {
      html += `<li data-nid="${response.nid}">${response.title}</li>`;
    });
    html += '</ul>';

    $container.html(html);
  }

  async function initSpLearningMod() {
    if (initialized) {
      return;
    }
    initialized = true;

    const $banner = $('#budget-banner');
    const hasBanner = $banner.length > 0;

    if (!hasBanner) {
      return;
    }

    const trackNid = $banner.attr('data-sp-track-nid');
    const settingsNid = drupalSettings?.sp_learningmod?.currentNodeId;
    const isTrackablePage = trackNid && trackNid !== '0' && trackNid !== '';

    if (isTrackablePage || settingsNid) {
      await trackCurrentNodeVisit();
    } else {
      await loadBudgetState();
    }

    await loadVisitedNodes();
    await loadSelectedResponses();
  }

  Drupal.behaviors.spLearningmodAjax = {
    attach: function (context, settings) {
      if (context !== document) {
        return;
      }
      initSpLearningMod();
    }
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      setTimeout(initSpLearningMod, 100);
    });
  } else {
    setTimeout(initSpLearningMod, 100);
  }

  Drupal.behaviors.spBudgetProgressBar = {
    attach: function (context, settings) {
      const $progressBar = $('#budget-progress-bar', context);
      const $table = $('[data-drupal-selector="edit-responses-table"]', context);

      if (!$progressBar.length || !$table.length) {
        return;
      }

      const costs = JSON.parse($table.attr('data-costs') || '{}');

      function updateProgressBar() {
        let totalCost = 0;

        $('[data-drupal-selector^="edit-responses-table-"]:checked').each(function () {
          const responseId = $(this).val();
          if (costs.hasOwnProperty(responseId)) {
            totalCost += parseInt(costs[responseId], 10);
          }
        });

        $progressBar.css('--progress-value', totalCost + '%');
        $progressBar.css('animation', 'none');
        $progressBar[0].offsetWidth;
        $progressBar.css('animation', 'fillProgress .5s ease-in-out forwards');
        $progressBar.text(totalCost + '%');

        $progressBar.removeClass('w3-green orange red');
        if (totalCost > 99) {
          $progressBar.addClass('red');
        } else if (totalCost >= 80) {
          $progressBar.addClass('orange');
        } else {
          $progressBar.addClass('w3-green');
        }
      }

      updateProgressBar();
      $table.find('input[type="checkbox"]').once('sp-budget-progress').on('change', updateProgressBar);
    }
  };

  Drupal.spLearningmod = {
    api: API,
    fetchApi: fetchApi,
    postApi: postApi,
    loadBudgetState: loadBudgetState,
    loadVisitedNodes: loadVisitedNodes,
    trackNodeVisit: trackCurrentNodeVisit,
    updateBudgetBanner: updateBudgetBanner,
    init: initSpLearningMod
  };

})(jQuery, Drupal, drupalSettings);
