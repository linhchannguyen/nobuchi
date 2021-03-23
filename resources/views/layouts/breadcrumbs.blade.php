@if(isset($breadcrumbs))
<div class="breadcrumbs">
    @foreach($breadcrumbs as $value)
    <a href="<?php echo url(''); ?>/{{$value[1]}}">{{ $value[0] }}</a> &nbsp; ＞ &nbsp; 
    @endforeach
    {{ $title }}
</div>
@endif