var flashdata = $('.flash-data').data('flashdata');
var flashdata2 = $('.flash-login').data('flashdata');

if (flashdata) {
	Swal.fire({
		title: 'Data Mahasiswa ',
		text: 'Berhasil ' + flashdata,
		icon: 'success'
	});
}

if (flashdata2) {
	Swal.fire({
		title: 'User Login ',
		text: flashdata2,
		icon: 'warning'
	});
}

// tombol-hapus

$('.tombol-hapus').on('click', function(e)
{
	e.preventDefault();

	var href = $(this).attr('href');
	Swal.fire({
	  title: 'Apakah anda yakin',
	  text: "data mahasiswa akan dihapus",
	  icon: 'warning',
	  showCancelButton: true,
	  confirmButtonColor: '#3085d6',
	  cancelButtonColor: '#d33',
	  confirmButtonText: 'Hapus Data'
	}).then((result) => {
	  if (result.value) {
	    document.location.href = href;
	  }
	})
});