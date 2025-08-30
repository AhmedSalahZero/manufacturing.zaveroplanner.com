<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<img src="{{asset('assets/images/logo2.png')}}" style="width:30% !important" class="logo" alt="ZAVERO">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>

