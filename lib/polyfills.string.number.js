// UMD 스타일: 브라우저 window, Node, ESM 어디서든 동작
(function (root, factory) {
  if (typeof module === 'object' && module.exports) {
    module.exports = factory();
  } else {
    root.phpLike = factory();
  }
}(typeof self !== 'undefined' ? self : this, function () {
  "use strict";

  // PHP str_replace(search, replace, subject)
  // - search/replace에 문자열 또는 배열 허용
  // - subject가 문자열이면 문자열 반환, 배열이면 배열 반환
  function str_replace(search, replace, subject) {
    const arrSearch = Array.isArray(search) ? search : [String(search)];
    const arrReplace = Array.isArray(replace) ? replace : [String(replace)];
    const map = new Map(arrSearch.map((s, i) => [String(s), String(arrReplace[i] ?? '')]));

    const doReplace = (s) => {
      let out = String(s);
      for (const [from, to] of map) {
        if (from === '') continue;
        // 전역 치환 (특수문자 이스케이프)
        const re = new RegExp(from.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g');
        out = out.replace(re, to);
      }
      return out;
    };

    if (Array.isArray(subject)) return subject.map(doReplace);
    return doReplace(subject);
  }

  //PHP number_format(number, decimals=0, decPoint='.', thousandsSep=',')
  function number_format(number, decimals = 0, decPoint = '.', thousandsSep = ',') {
    const n = +number;
    if (!isFinite(n)) return '0';

    const d = Math.min(Math.max(+decimals, 0), 20); // 안전 범위
    // 고정 소수점 문자열 (반올림 포함)
    let s = n.toFixed(d);

    // 소수점/정수부 분리
    let [intPart, fracPart = ''] = s.split('.');
    // 음수 처리를 위해 부호 분리
    const sign = intPart.startsWith('-') ? '-' : '';
    if (sign) intPart = intPart.slice(1);

    // 천 단위 구분
    intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, thousandsSep);

    // 소수부 조립
    if (d > 0) {
      // padEnd 보정
      fracPart = (fracPart || '').padEnd(d, '0');
      return `${sign}${intPart}${decPoint}${fracPart}`;
    }
    return `${sign}${intPart}`;
  }

  // 전역 오염 방지: 필요한 경우에만 window에 등록
  if (typeof window !== 'undefined') {
    window.phpLike = window.phpLike || {};
    if (typeof window.str_replace !== 'function') window.str_replace = str_replace;
    if (typeof window.number_format !== 'function') window.number_format = number_format;
  }

  return { str_replace, number_format };
}));
