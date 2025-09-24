;(function (w) {
  // ===== 이미지 파일 검사 =====
  // 사용법: if (!CommonCheckImage($(this))) return;
  if (typeof w.CommonCheckImage !== 'function') {
    w.CommonCheckImage = function ($input) {
      try {
        var el   = $input && $input[0];
        var file = el && el.files && el.files[0];
        if (!file) {
          (w.alertify?.error || alert)("이미지 파일을 선택하세요.");
          return false;
        }

        // 허용 확장자: input의 accept, data-allow, 기본값(.jpg,.jpeg,.png,.gif)
        var acceptStr = ($input.attr('accept') || $input.data('allow') || '.jpg,.jpeg,.png,.gif')
          .toLowerCase()
          .replace(/\s/g, '');
        // ex) ".jpg,.jpeg,.png" -> ["jpg","jpeg","png"]
        var allowed = acceptStr.split(',').map(function (x) { return x.replace(/^\./, ''); }).filter(Boolean);

        var name = (file.name || '').toLowerCase();
        var ext  = name.indexOf('.') >= 0 ? name.split('.').pop() : '';
        if (!ext || allowed.indexOf(ext) === -1) {
          (w.alertify?.error || alert)("이미지 파일(" + allowed.join(', ') + ")만 등록 가능합니다.");
          $input.val('');
          return false;
        }

        // 용량 제한: data-max-size(바이트) 지정, 없으면 10MB
        var maxSize = parseInt($input.data('maxSize'), 10);
        if (!(maxSize > 0)) maxSize = 10 * 1024 * 1024; // 10MB
        if (file.size > maxSize) {
          (w.alertify?.error || alert)("파일 용량이 " + Math.round(maxSize / (1024*1024)) + "MB를 초과했습니다.");
          $input.val('');
          return false;
        }

        return true;
      } catch (e) {
        console.error(e);
        (w.alertify?.error || alert)("이미지 확인 중 오류가 발생했습니다.");
        return false;
      }
    };
  }

  // ===== 엑셀 파일 검사 =====
  // 사용법: if (!CommonCheckExcel($(this))) return;
  if (typeof w.CommonCheckExcel !== 'function') {
    w.CommonCheckExcel = function ($input) {
      try {
        var el   = $input && $input[0];
        var file = el && el.files && el.files[0];
        if (!file) {
          (w.alertify?.error || alert)("엑셀 파일을 선택하세요.");
          return false;
        }
        var name = (file.name || '').toLowerCase();
        if (!/\.(xls|xlsx)$/.test(name)) {
          (w.alertify?.error || alert)("엑셀 파일(xls, xlsx)만 등록 가능합니다.");
          $input.val('');
          return false;
        }
        // 기본 20MB, data-max-size로 변경 가능
        var maxSize = parseInt($input.data('maxSize'), 10);
        if (!(maxSize > 0)) maxSize = 20 * 1024 * 1024;
        if (file.size > maxSize) {
          (w.alertify?.error || alert)("파일 용량이 " + Math.round(maxSize / (1024*1024)) + "MB를 초과했습니다.");
          $input.val('');
          return false;
        }
        return true;
      } catch (e) {
        console.error(e);
        (w.alertify?.error || alert)("파일 확인 중 오류가 발생했습니다.");
        return false;
      }
    };
  }
})(window);
