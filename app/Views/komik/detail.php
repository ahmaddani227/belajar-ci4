<?= $this->extend('layout/template'); ?>

<?= $this->section('content'); ?>
<div class="container">
    <div class="row">
        <div class="col">
            <h1 class="my-2">Detail Komik</h1>
            <div class="card mb-3" style="max-width: 540px;">
                <div class="row g-0">
                    <div class="col-md-4">
                        <img src="/img/<?= $detail['sampul']; ?>" class="img-fluid rounded-start" alt="...">
                    </div>
                    <div class="col-md-8">
                        <div class="card-body">
                            <h5 class="card-title"><?= $detail['judul'] ?></h5>
                            <p class="card-text"><b>Penulis : </b><?= $detail['penulis']; ?></p>
                            <p class="card-text"><b>Penerbit : </b><?= $detail['penerbit']; ?></p>

                            <a href="/komik/edit/<?= $detail['slug']; ?>" class="btn btn-warning">Edit</a>

                            <form action="/komik/<?= $detail['id']; ?>" method="post" class="d-inline">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn-danger"
                                    onclick="return confirm('Apakah anda yakin ?')">Delete</button>
                            </form>

                            <br><br>

                            <a href="/komik">Kembali ke Daftar komik</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection('content'); ?>