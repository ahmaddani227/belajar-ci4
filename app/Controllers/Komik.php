<?php

namespace App\Controllers;

use App\Models\KomikModel;

class Komik extends BaseController
{
    protected $KomikModel;
    protected $session;
    protected $validation;

    public function __construct()
    {
        $this->KomikModel = new KomikModel();
        $this->session = \Config\Services::session();
        $this->validation = \Config\Services::validation();
    }
    
    public function index()
    {
        $komik = $this->KomikModel->getKomik();
        $data = [
            'title' => 'Daftar Komik',
            'komik' => $komik
        ];

        return view('komik/index', $data);
    }

    public function detail($slug)
    {
        $data = [
            'title' => 'Detail Komik',
            'detail' => $this->KomikModel->getKomik($slug)
        ];

        // cek jika komik tidak ada
        if( empty($data['detail']) ){
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Judul Komik ' .$slug. ' tidak ada');
        }

        return view('komik/detail', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Form Tamba Data Komik',
            'validation' => $this->validation
        ];

        return view('komik/create', $data);
    }

    public function save()
    {
        if( !$this->validate([
            'judul' => [
                'rules'  => 'required|is_unique[komik.judul]',
                'errors' => [
                    'required' => '{field} komik harus diisi',
                    'is_unique' => '{field} komik sudah terdaftar'
                ]
            ],
            'sampul' => [
                'rules'  => 'max_size[sampul,1024]|is_image[sampul]|mime_in[sampul,image/jpg,image/jpeg,image/png]',
                'errors' =>  [
                    'max_size' => 'Ukuran gambar terlalu besar',
                    'is_image' => 'Yang Anda pilih bukan gambar',
                    'mime_in'  => 'Yang Anda pilih bukan gambar'
                ]  
            ]
        ])) {
            // $validation = $this->validation;
            return redirect()->to('/komik/create')->withInput();
        }

        // ambil gambar
        $fileSampul = $this->request->getFile('sampul');
        // dd($fileSampul);

        // cek apakah tidak ada gamber yang diupload
        if( $fileSampul->getError() == 4 ){
            $namaSampul = 'default.jpg';
        }else{
            // generate nama sampul random
            $namaSampul = $fileSampul->getRandomName();
            
            // pindahkan file ke folder img
            // $fileSampul->move('img');
    
            // pindahkan file ke folder img(jika memakai nama sampul random)
            $fileSampul->move('img', $namaSampul);
            
            // ambil nama file sampul
            // $namaSampul = $fileSampul->getName();
        }


        $slug = url_title($this->request->getVar('judul'), '-', true);
        $this->KomikModel->save([
            'judul'    => $this->request->getVar('judul'),
            'slug'     => $slug,
            'penulis'  => $this->request->getVar('penulis'),
            'penerbit' => $this->request->getVar('penerbit'),
            'sampul'   => $namaSampul,
        ]);

        $this->session->setFlashdata('pesan', 'Data berhasil ditambahkan');

        return redirect()->to('/komik');
    }

    public function delete($id)
    {
        // cari gambar berdasarkan id
        $komik = $this->KomikModel->find($id);

        // cek jika file gambarnya default.jpg
        if( $komik['sampul'] != 'default.jpg' ){
            // hapus file gambar
            unlink('img/' . $komik['sampul']);
        }

        $this->KomikModel->delete($id);

        $this->session->setFlashdata('pesan', 'Data berhasil dihapus');

        return redirect()->to('/komik');
    }

    public function edit($slug)
    {
        $data = [
            'title' => 'Form Ubah Data Komik',
            'validation' => $this->validation,
            'komik' => $this->KomikModel->getKomik($slug)
        ];

        return view('komik/edit', $data);
    }

    public function update($id)
    {
        // cek judul
        $komikLama = $this->KomikModel->getKomik($this->request->getVar('slug'));
        if( $komikLama['judul'] == $this->request->getVar('judul') ){
            $rule_judul = 'required';
        }else{
            $rule_judul = 'required|is_unique[komik.judul]';
        }

        // validation
        if( !$this->validate([
            'judul' => [
                'rules'  => $rule_judul,
                'errors' => [
                    'required' => '{field} komik harus diisi',
                    'is_unique' => '{field} komik sudaf terdaftar'
                ]
            ],
            'sampul' => [
                'rules'  => 'max_size[sampul,1024]|is_image[sampul]|mime_in[sampul,image/jpg,image/jpeg,image/png]',
                'errors' =>  [
                    'max_size' => 'Ukuran gambar terlalu besar',
                    'is_image' => 'Yang Anda pilih bukan gambar',
                    'mime_in'  => 'Yang Anda pilih bukan gambar'
                ]  
            ]
        ])) {
            // $validation = $this->validation;
            return redirect()->to('/komik/edit/' . $this->request->getVar('slug'))->withInput();
        }

        $fileSampul = $this->request->getFile('sampul');
        $sampulLama = $this->request->getVar('sampulLama');

        // cek gambar, apakah tetap gambar lama
        if( $fileSampul->getError() == 4 ){
            $namaSampul = $sampulLama;
        }else{
            // generate nama sampul random
            $namaSampul = $fileSampul->getRandomName();
            
            // pindahkan file ke folder img
            // $fileSampul->move('img');
    
            // pindahkan(upload) file ke folder img(jika memakai nama sampul random)
            $fileSampul->move('img', $namaSampul);
            
            // ambil nama file sampul $this->request->getVar('sampulLama');
            // $namaSampul = $fileSampul->getName();

            // hapus file lama
            unlink('img/' . $sampulLama);
        }

        // dd($this->request->getVar());
        $slug = url_title($this->request->getVar('judul'), '-', true);
        $this->KomikModel->save([
            'id'       => $id,
            'judul'    => $this->request->getVar('judul'),
            'slug'     => $slug,
            'penulis'  => $this->request->getVar('penulis'),
            'penerbit' => $this->request->getVar('penerbit'),
            'sampul'   => $namaSampul
        ]);

        $this->session->setFlashdata('pesan', 'Data berhasil diubah');

        return redirect()->to('/komik');
    }
}