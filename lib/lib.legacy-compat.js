// /common/js/legacy-compat.js
;(function (w) {
  // PHP str_replace 간단 호환 (배열도 일부 대응)
  if (typeof w.str_replace !== 'function') {
    w.str_replace = function (search, replace, subject) {
      let s = (subject ?? '').toString();
      if (Array.isArray(search)) {
        for (let i = 0; i < search.length; i++) {
          s = s.split(search[i]).join(Array.isArray(replace) ? (replace[i] ?? '') : replace);
        }
        return s;
      }
      return s.split(search).join(replace);
    };
  }

  // PHP number_format 대체
  if (typeof w.number_format !== 'function') {
    w.number_format = function (n, decimals = 0) {
      const num = parseFloat(String(n ?? '').replace(/,/g, '')) || 0;
      const d    = (decimals|0) >= 0 ? (decimals|0) : 0;
      const fixed = num.toFixed(d);
      return fixed.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    };
  }

  // 보조 유틸(있으면 그대로, 없으면만 정의)
  if (typeof w.uncomma   !== 'function') w.uncomma   = s => String(s ?? '').replace(/,/g,'');
  if (typeof w.toInt     !== 'function') w.toInt     = s => parseInt(w.uncomma(s), 10) || 0;
  if (typeof w.toFloat   !== 'function') w.toFloat   = s => parseFloat(w.uncomma(s)) || 0;
  if (typeof w.priceLimit2 !== 'function') w.priceLimit2 = n => Math.round(w.toFloat(n));
})(window);
