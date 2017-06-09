<template>
	<div ref="alert" class="alert" v-bind:class="[ alertClass.dismissible, alertClass.type ]" role="alert">
		<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		<strong>{{ header }}</strong> {{ message }}
	</div>
</template>
<script>
	export default {
		name: 'alert',
		props: {
			header: {type: String, default: ''},
			message: {type: String, default: ''},
			type: {type: String, default: 'danger'},
			dismissible: {type: Boolean, default: true}
		},
		mounted: function() {
			 if (this.dismissible) {
				 this.alertClass.dismissible = 'alert-dismissible';
			 }

			this.alertClass.type = 'alert-' + this.type;
		},
		data: function() {
			return {
				alertClass: {
					dismissible: '',
					type: ''
				}
			}
		}
	}
</script>