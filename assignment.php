<?php
    session_start();
    $error = '';
    $avatar = '';
    $filename = '';
    $updatekey = '';
    $totalusers = array();
    if(!isset($_SESSION['sessioncount'])){
        $_SESSION['sessioncount'] = 0;
    }
    $viewrow = json_encode(array());
    if(!isset($_SESSION)){
        $_SESSION['totalusers'] = array();
        $_SESSION['sessioncount']++;
    }elseif($_SESSION['sessioncount'] <= 0){
        $_SESSION['totalusers'] = array();
        $_SESSION['sessioncount']++;
    }
    ## create ##
    if(isset($_POST['userformsubmit'])){
        if(!empty(@$_POST['username']) && !empty(@$_POST['gender'])){
            $name = $_POST['username'];
            $gender = $_POST['gender'];
            $address = @$_POST['address'];
            $oldimg = @$_POST['oldimage'];
            $oldname = @$_POST['oldname'];
            ## image ##
            $file_name = $_FILES['profileimage']['name'];
            $file_tmp = $_FILES['profileimage']['tmp_name'];
            if($file_name != '' && $file_tmp != ''){
                $type = pathinfo($file_tmp, PATHINFO_EXTENSION);
                $data = file_get_contents($file_tmp);
                $avatar = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
            $updatekey = @$_POST['editrowid'];
            if(isset($updatekey) && ($updatekey != '')){
                $avatar = ($avatar == '') ? $oldimg : $avatar;
                $filename = ($file_name == '' ? $oldname : $file_name);
                $_SESSION['totalusers'][$updatekey]['name'] = $name;
                $_SESSION['totalusers'][$updatekey]['gender'] = $gender;
                $_SESSION['totalusers'][$updatekey]['address'] = $address;
                $_SESSION['totalusers'][$updatekey]['avatar'] = $avatar;
                $_SESSION['totalusers'][$updatekey]['filename'] = $filename;
            }else{
                if(count($_SESSION['totalusers']) <= 0){
                    array_push($_SESSION['totalusers'], [
                        'name' => $name,
                        'gender' => $gender,
                        'address' => $address,
                        'avatar' => $avatar,
                        'filename' => $file_name,
                    ]);
                }else{
                    for($i = 0;$i < count($_SESSION['totalusers']);$i++):
                        if(!in_array($name, array_column($_SESSION['totalusers'], 'name'))){
                            array_push($_SESSION['totalusers'], [
                                'name' => $name,
                                'gender' => $gender,
                                'address' => $address,
                                'avatar' => $avatar,
                                'filename' => $file_name,
                            ]);
                            break;
                        }else{
                            $error = 'Error occured: Duplicate entry with same name.';
                        }
                    endfor; 
                }
            }
            $url = strtok($_SERVER['REQUEST_URI'], '?');
            header('Location: '.$url);
        }else{
            $error = 'Error occured: User name and gender required.';
        }
    }
    ## delete ##
    if(isset($_GET['remove'])){
        $removeKey = base64_decode($_GET['remove']);
        if($removeKey != ''){
            unset($_SESSION['totalusers'][$removeKey]);
        }
    }
    ## view ##
    if(isset($_GET['rowid'])){
        $viewkey = base64_decode($_GET['rowid']);
        $viewrow = json_encode($_SESSION['totalusers'][$viewkey]);
    }
    ## edit ##
    if(isset($_GET['editid']) && ($updatekey == '')){
        $editkey = base64_decode($_GET['editid']);
        $edituser = array();
        $edituser = $_SESSION['totalusers'][$editkey];
        $edituser['id'] = $editkey;
        $viewrow = json_encode($edituser);
    }
    ## id sort ##
    if(isset($_GET['idshort'])){
        $_GET['idshort'] == 'DESC' ? krsort($_SESSION['totalusers']) : ksort($_SESSION['totalusers']);
    }
    ## name sort ##
    if(isset($_GET['nameshort'])){
        if($_GET['nameshort'] == 'DESC'){
            array_multisort(array_column($_SESSION['totalusers'], 'name'), SORT_DESC, $_SESSION['totalusers']);
        }else{
            array_multisort(array_column($_SESSION['totalusers'], 'name'), SORT_ASC, $_SESSION['totalusers']);
        }
    }
    ## session destroy ##
    if(isset($_GET['sessionend'])){
        session_destroy();
    }
    ## object ##
    if(isset($_SESSION['totalusers']) > 0){
        $totalusers = json_decode(json_encode($_SESSION['totalusers']));
    }
    // print_r($totalusers);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignment</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-dark@4/dark.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <style>
        .error{
            color: red;
        }
        .img {
            height: 50px;
            width: 50px;
            border-radius: 10%;
        }
        .req{
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center"></h1>
        <p class="error"><?= $error ?></p>
        <button class="btn btn-success" onclick="addModel()">Add User</button>
        <a href="assignment.php"><button class="btn btn-primary ml-2">Reset</button></a>
        <?php $idshorttype = (@$_GET['idshort'] == '') ? 'ASC' : ((@$_GET['idshort'] == 'DESC') ? 'ASC' : 'DESC'); ?>
        <a href="?idshort=<?= $idshorttype?>"><button class="btn btn-info">ID Short <?= @$_GET['idshort']?></button></a>
        <?php $nameshorttype = (@$_GET['nameshort'] == '') ? 'DESC' : ((@$_GET['nameshort'] == 'ASC') ? 'DESC' : 'ASC');?>
        <a href="?nameshort=<?= $nameshorttype?>"><button class="btn btn-warning">Name Short <?= @$_GET['nameshort']?></button></a>
        <a href="?sessionend=true"><button class="btn btn-danger">Session Destroy</button></a>
        <div class="tabel-responsive mt-2">
            <table class="table table-stripped table-bordered mt-2" style="overflow-y:scroll;width: 100%;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th class="text-center">Image</th>
                        <th>Gender</th>
                        <th>Address</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(isset($totalusers) && !empty(@$totalusers)):?>
                        <?php $index = 1; ?>
                        <?php foreach(@$totalusers as $key => $tu):?>
                            <tr>
                                <td><?= $key+1 ?>)</td>
                                <td><?= $tu->name ?></td>
                                <?php if(@$tu->avatar != ''):?>
                                    <td class="text-center"><?= @$tu->filename != '' ? @$tu->filename.', ' : ''?><a href="<?= @$tu->avatar ?>"><img src="<?= @$tu->avatar ?>" class="img" alt=""></a></td>
                                <?php else: ?>
                                    <td class="text-center">no avatar</td>
                                <?php endif; ?>
                                <td><?= $tu->gender ?></td>
                                <td style="white-space: pre;"><?= ($tu->address == '') ? 'not given' : $tu->address ?></td>
                                <td>
                                    <a href="?editid=<?= base64_encode($key)?>"><i class="fa fa-edit"></i></a>
                                    <a href="?rowid=<?= base64_encode($key)?>"><i class="fa fa-eye"></i></a>
                                    <a href="javascript:void(0);" onclick="trash('<?= base64_encode($key)?>')"><i class="fa fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td class="text-center" colspan="6">No Data Found.</td></tr>
                    <?php endif;?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- add new modal  -->
    <div class="modal" tabindex="-1" id="addusermodal" role="dialog">
        <div class="modal-dialog" role="document">
            <form action="" method="POST" id="userinputform" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><span id="formtitle">Add New User</span></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="">User Name <span class="req">*</span></label>
                                <input type="text" name="username" id="username" class="form-control" placeholder="User Full Name" require>
                            </div>
                            <div class="col-md-6">
                                <label for="">Gender <span class="req">*</span></label>
                                <select name="gender" id="gender" class="form-control" require>
                                    <option selected readonly disabled>choose one</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-10">
                                <label for="">Image</label>
                                <input type="file" name="profileimage" id="profileimage" class="form-control" accept="image/*">
                            </div>
                            <input type="hidden" name="oldimage" id="oldimage">
                            <input type="hidden" name="oldname" id="oldname">
                            <div class="col-md-2 oldimgdiv">
                                <a href="" class="oldimga"><img src="" class="oldimg img" style="margin-top: 9px;" alt=""></a>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <label for="">Address</label>
                                <textarea name="address" id="address" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="editrowid" id="editid">
                    <div class="modal-footer" id="modalbtns">
                        <button type="submit" name="userformsubmit" class="btn btn-primary" onclick="closeModal()">Save changes</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
<script>
    var viewrowdata = '<?= $viewrow ?>';
    if(viewrowdata){
        view(viewrowdata);
    }
    function addModel(){
        resetmodal()
        $('#addusermodal').modal('show');
    }
    function closeModal(){
        $('#addusermodal').modal('hide');
    }
    function resetmodal(){
        $('#username').val('');
        $('#gender').val($('#gender option:first').val());
        $('.oldimga').attr('href', '');
        $('.oldimg').attr('src', '');
        $('#address').val('');
        $('#editid').val('');
        $('#oldimage').val('');
        $('#oldname').val('');
        $('#modalbtns').show();
        $('.oldimgdiv').hide();
        $('#formtitle').html('Add New User');
    }
    function view(viewrowdata){
        let obj = JSON.parse(viewrowdata);
        if(obj.hasOwnProperty('name') && obj.hasOwnProperty('gender')){
            $('#username').val(obj.name);
            $('#gender').val(obj.gender).attr('selected', true);
            if(obj.avatar != ''){
                $('.oldimgdiv').show();
                $('.oldimga').attr('href', obj.avatar);
                $('.oldimg').attr('src', obj.avatar);
            }else{
                $('.oldimgdiv').hide();
            }
            $('#oldimage').val(obj.avatar);
            $('#oldname').val(obj.filename);
            $('#address').val(obj.address);
            if(obj.hasOwnProperty('id') && (obj.id != '')){
                $('#editid').val(obj.id);
                $('#modalbtns').show();
                $('#formtitle').html('Edit '+obj.name+' ');
            }else{
                $('#modalbtns').hide();
                $('#formtitle').html('View '+obj.name+' detail');
            }
            $('#addusermodal').modal('show');
        }
    }
    function trash(rowid){
        Swal.fire({
            title: "Do you want to save the changes?",
            showDenyButton: true,
            confirmButtonText: "Delete",
            denyButtonText: `Don't Delete`
        }).then((result) => {
            if (result.isConfirmed) {
                location.href = "?remove=" + rowid;
            }
        });
    }
    setTimeout(() => {
        $('.error').html('');
    }, 2500);
</script>
</html>