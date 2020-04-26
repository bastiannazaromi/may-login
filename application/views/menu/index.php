<!-- Begin Page Content -->
<div class="container-fluid">

  <!-- Page Heading -->
  <h1 class="h3 mb-4 text-gray-800"><?= $title ; ?></h1>

  <div class="flash-data" data-flashdata="<?= $this->session->flashdata('flash') ; ?>"></div>

  <div class="row">
  	<div class="col-lg">

		<?= form_error('menu', '<div class="alert alert-danger" role="alert">', '</div>') ; ?>

		<a href="" class="btn btn-primary" data-toggle="modal" data-target="#addMenu">Add New Menu</a>

  		<table class="table table-hover">
		  <thead>
		    <tr>
		      <th scope="col">#</th>
		      <th scope="col">Mehu</th>
		      <th scope="col">Action</th>
		    </tr>
		  </thead>
		  <tbody>
		  	<?php $no= 1; foreach($menu as $m) : ?>
			    <tr>
			      <th scope="row"><?= $no++ ; ?></th>
			      <td><?= $m['menu'] ; ?></td>
			      <td>
			      	<a href="" class="badge badge-warning">Edit</a>
			      	<a href="" class="badge badge-danger">Delete</a>
			      </td>
			    </tr>
			<?php endforeach ; ?>
		  </tbody>
		</table>

  	</div>
  </div>

</div>

<!-- modal add menu -->

<!-- Modal -->
<div class="modal fade" id="addMenu" tabindex="-1" role="dialog" aria-labelledby="addMenuLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addMenuLabel">Add New Menu</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="<?= base_url('Menu') ; ?>" method="post">
	      <div class="modal-body">
        	<div class="form-group">
		    <input type="text" class="form-control" id="menu" placeholder="Menu name" name="menu">
		  </div>
	      </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
	        <button type="submit" class="btn btn-primary">Add</button>
	      </div>
      </form>
    </div>
  </div>
</div>