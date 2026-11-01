@if (session('ok'))
  <div class="toast ok" role="status">{{ session('ok') }}</div>
@endif
@if (session('err'))
  <div class="toast bad" role="alert">{{ session('err') }}</div>
@endif
