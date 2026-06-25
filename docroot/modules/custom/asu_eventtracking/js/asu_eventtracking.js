/**
 * GA tracking helper.
 *
 * @param callback
 * @param opt_timeout
 * @returns {(function(): void)|*}
 */
function createFunctionWithTimeout(callback, opt_timeout) {
  var called = false;
  setTimeout(callback, opt_timeout || 1000);

  return function() {
    if (!called) {
      called = true;
      callback();
    }
  };
}
