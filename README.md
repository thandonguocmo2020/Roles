# ENTRUST (Laravel 5 Package)

[![Build Status](https://travis-ci.org/Zizaco/entrust.svg)](https://travis-ci.org/Zizaco/entrust)
[![Version](https://img.shields.io/packagist/v/Zizaco/entrust.svg)](https://packagist.org/packages/zizaco/entrust)
[![License](https://poser.pugx.org/zizaco/entrust/license.svg)](https://packagist.org/packages/zizaco/entrust)
[![Total Downloads](https://img.shields.io/packagist/dt/zizaco/entrust.svg)](https://packagist.org/packages/zizaco/entrust)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/cc4af966-809b-4fbc-b8b2-bb2850e6711e/small.png)](https://insight.sensiolabs.com/projects/cc4af966-809b-4fbc-b8b2-bb2850e6711e)

Entrust là một cách gọn gàng và linh hoạt để thêm vai trò người dùng  Role-based và quyền hạn trong  **Laravel 5**.

If you are looking for the Laravel 4 version, take a look [Branch 1.0](https://github.com/Zizaco/entrust/tree/1.0). It
contains the latest entrust version for Laravel 4.

## Contents


- [Cài đặt](#installation)
- [Fix Errors](#fixError)
- [Cấu hình](#configuration)
    - [Sử dụng mối quan hệ từ roles](#user-relation-to-roles)
    - [Models](#models)
        - [Role](#role)
        - [Permission](#permission)
        - [Người sử dụng User](#user)
        - [Xóa  Deleting](#soft-deleting)
        - [Cập nhập ]
- [Sử dụng](#usage)
    - [Các khái niệm - Gán vai trò cho user ](#concepts)
        - [Kiểm tra Vai trò & Quyền - Checking for Roles & Permissions](#checking-for-roles--permissions)
        - [Tư cách người dùng](#user-ability)
    - [Blade templates](#blade-templates)
    - [Middleware](#middleware)
    - [Cú pháp ngắn gọn cho bộ lọc route ](#short-syntax-route-filter)
    - [Route filter](#route-filter)
- [Xử lý sự cố](#troubleshooting)
- [giấy phép](#license)
- [hướng dẫn đóng góp](#contribution-guidelines)
- [Thông tin thêm](#additional-information)

## Installation

Để cài đặt Laravel 5 Entrust, chỉ cần thêm

    "zizaco/entrust": "5.2.x-dev"

từ  composer.json của bạn. Chạy lệnh `composer install` hoặc `composer update`.

Hoặc bạn có thể chạy lệnh yêu cầu gói  bằng  `composer require` từ terminal của bạn:
    
    composer require zizaco/entrust:5.2.x-dev
    
Trong file `config/app.php` thêm mới
```php
    Hoanghiep\Role\EntrustServiceProvider::class,
```
hãy thêm nó trong `providers` mảng và 
```php
    'Entrust'   => Hoanghiep\Role\EntrustFacade::class,
```
trong  `aliases` mảng.

Bạn có thể sử dụng Middleware  [Middleware](#middleware) (yêu cầu từ Laravel 5.1 trở về sau) bạn cũng cần phải thêm
```php
    'role' => \Hoanghiep\Role\Middleware\EntrustRole::class,
    'permission' => \Hoanghiep\Role\Middleware\EntrustPermission::class,
    'ability' => \Hoanghiep\Role\Middleware\EntrustAbility::class,
```
trong `routeMiddleware` mảng tại đường dẫn `app/Http/Kernel.php`.

## Configuration

thiết lập các giá trị tài sản property values trong `config/auth.php`.
Những giá trị này sẽ được sử dụng bởi ủy thác để tham khảo bảng use và model.

config/auth.php thay đổi providers key thành

	**
	'providers' => [
	        'users' => [
	            'driver' => 'eloquent',
	            'model' => App\User::class,
	            'table' => 'users',
	        ],
	],
	**


Bạn cũng có thể xuất bản các cấu hình cho các gói này để tùy chỉnh thêm các tên bảng và không gian tên mô hình.
Sử dụng câu lệnh `php artisan vendor:publish` và 1 `entrust.php` file mở rộng được tại ra tại app/config directory.

### User relation to roles

Bây giờ tạo ra một migration tạo cấu trúc bảng  Entrust migration bằng lệnh:

```bash
php artisan entrust:migration
```

Nó sẽ tạo ra `<timestamp>_entrust_setup_tables.php` file migration.
Bây giờ bạn có thể chạy nó với lệnh :

```bash
php artisan migrate
```

Sau đó các bảng mới sẽ có mặt :
- `roles` &mdash; BẢNG CHỨA VAI TRÒ CỦA NGƯỜI DÙNG TRÊN HỆ THỐNG. VÍ DỤ USER HIỆN TẠI LÀ :. "admin","Chủ dự án", "Nhân viên công ty"..v.v
- `permissions` &mdash; CHỨA THÔNG TIN VỀ QUYỀN HẠN VỚI CÁC LAYOUT TRONG HỆ THỐNG VÍ DỤ NGƯỜI DÙNG LÀ CHỦ DỰ ÁN CÓ QUYỀN.Thêm - Sửa Xóa.  ví dụ "create-post","edit-user"..v.v. phù hợp từng vai trò sẽ có quyền phù hợp.
- `role_user` &mdash; Chứa [many-to-many](http://laravel.com/docs/4.2/eloquent#many-to-many) quan hệ giữa người sử dụng và vai trò của họ trên hệ thống. roles và  users
- `permission_role` &mdash; Chứa [many-to-many](http://laravel.com/docs/4.2/eloquent#many-to-many) quan hệ giữa vai trò và quyền hạn phù hợp nó.

### Models

#### Role

Tạo ra một Model Role `app/models/Role.php` để áp dụng trong ví dụ :

```php
<?php namespace App;

use Hoanghiep\Role\EntrustRole;

class Role extends EntrustRole
{
}
```

File vai trò `Role` model có ba thuộc tính chính:
- `name` &mdash; tên vai trò là định danh duy nhất. Mô tả người dùng hiện tại là ai trên hệ thống . Ví dụ: "admin", "chủ nhân", "Nhân viên".
- `display_name` &mdash; tên có thể đọc để hiểu được không nhất thiết là duy nhât nó là tùy chọn ví dụ: "User Administrator", "Chủ dự án", "Nhân viên công ty".
- `description` &mdash; Một lời giải thích chi tiết hơn về những gì vai trò.

Ngoài ra cả 2 colum `display_name` và `description` là tùy chọn; nó có thể chứa nullable trong cơ sở dữ liệu.

#### Permission

Bảng chứa quyền hạn của vai trò name. Các chức năng phù hợp của vai trò tương ứng để cho phép sử dụng các layout phân luồng phù hợp.
Tạo ra một Permission model file trong  `app/models/Permission.php` để sử dụng trong ví dụ :
```
	<?php namespace App;
	
	use Hoanghiep\Role\EntrustPermission;
	
	class Permission extends EntrustPermission
	{
	}
```

Trong quyền hạn `Permission` model có các thuộc tính giống như  `Role`:
- `name` &mdash; name là định danh duy nhất, để cho biết các quyền hạn phù hợp với vai trò  tương ứng. ví dụ : "create-post", "edit-user", "post-payment", "mailing-list-subscribe".
- `display_name` &mdash; Tên mà người sử dụng đọc được về quyền của họ. ví dụ  "Create Posts", "Edit Users", "Post Payments", "Subscribe to mailing list".
- `description` &mdash; Chứa mô tả về thông tin cụ thể của quyền hạn

In general, it may be helpful to think of the last two attributes in the form of a sentence: "The permission `display_name` allows a user to `description`."

#### User

Tiếp theo, sử dụng  `EntrustUserTrait` trait trong  model `User` hiện có của bạn. để kết nối người dùng và gán vai trò ví dụ:

```
	<?php
	
	namespace App;
	
	use Illuminate\Foundation\Auth\User as Authenticatable;
	use Hoanghiep\Role\Traits\EntrustUserTrait;
	
	class User extends Authenticatable
	{
	
	     use EntrustUserTrait;
	

```

Điều này sẽ cho phép các mối quan hệ với `Role` và thêm các phương thức sau đây  `function roles()`,  `function hasRole($name)`, `function can($permission)`, và  `function ability($roles, $permissions, $options)` trong  `User` model của bạn.

Cuối cùng chạy lệnh  composer autoload :

```bash
composer dump-autoload
```

**Và bạn đã sẵn sàng để đi tiếp.**

#### Soft Deleting

Xóa mềm.

Theo mặc định migration có thể sử dụng  `onDelete('cascade')` dùng để xóa các bản ghi mà có mối quan hệ với các bản ghi cha mẹ "parent" đã bị xóa. nếu vì một lý do nào đó cơ sở dữ liệu của bạn không thể sử dụng  cascading deletes,

Các class EntrustRole và EntrustPermission ,và HasRole trait nạp vào một event listeners nắng nghe  sự kiện để xóa các bản ghi trong các bảng liên quan. Nếu trong các trường hợp bạn không phụ thuộc dữ liệu bị xóa. các event listeners sẽ không xóa dữ liệu liên tục khi bạn sử dụng soft deleting

Tuy nhiên do hạn chế về event listeners, không có cách nào để phân biệt một cuộc gọi function `delete()` so với cuộc gọi `forceDelete()`. Vì lý do này, **Trước khi bạn bắt buộc  delete 1 dữ liệu cha từ model, bạn phải tự xóa bất kỳ dữ liệu quan hệ con** (ngoại trừ khi bạn sử dụng  bảng có cột chứa cascading deletes"

```php
$role = Role::findOrFail(1); // lấy vai trò của một id nhất định

// xóa liên tục cùng các dữ liệu có quan hệ
$role->delete(); // Điều này sẽ làm việc không có vấn đề gì

// Băt buộc Delete
$role->users()->sync([]); // Xóa dữ liệu con có quan hệ của users
$role->perms()->sync([]); // xóa dữ liệu con có quan hệ của perms

$role->forceDelete(); // bắt buộc xóa bất kể các có cascading delete không cho phép xóa. "cascading delete" yêu cầu xóa các dữ liệu liên quan trước khi xóa bản ghi."
```

### update

$user->roles()->sync($roleKeys)

sync() phương pháp này chạy trên một mối quan hệ phương pháp đã được định nghĩa

$roleKeys là khóa role_id có trong bảng trung gian. ví dụ :

`$user = $this->userRepository->find($id)`;

`$user->roles()->sync([$request->role_id])`;

điều này sẽ truy cập vào mối quan hệ nhiều nhiều giữa bảng user và bảng role qua 1 bảng trung gian lưu trữ. 

bảng trung gian ở đây là bảng role_user nó sẽ đồng bộ lại bằng cách tìm user id của nó và và  cập nhập vai trò qua khóa 
role_id



## Usage

Sử dụng 

### Concepts

Các khái niệm

Hãy bắt đầu bằng cách tạo ra những điều sau đây vai trò và phân quyền user. `Role`s and `Permission`s:

```php
$owner = new Role();
$owner->name         = 'owner';
$owner->display_name = 'Project Owner'; // tùy chọn có hoặc không
$owner->description  = 'User is the owner of a given project'; // tùy chọn có hoặc không
$owner->save();

$admin = new Role();
$admin->name         = 'admin';
$admin->display_name = 'User Administrator'; // tùy chọn có hoặc không
$admin->description  = 'User is allowed to manage and edit other users'; // tùy chọn có hoặc không
$admin->save();
```
Tiếp theo, với cả hai vai trò tạo ra chúng ta hãy gán cho người sử dụng user.

Nhờ `HasRole` trait  điều này sẽ thực hiện dễ dàng giống như :

```php
$user = User::where('username', '=', 'michele')->first();

// gán vai trò 

$user->attachRole($admin); // tham số có thể là một đối tượng Role object, array mảng, hoặc id

ví dụ : 
        $user = User::where('name', '=', 'Hoang Hiep')->first();
	    $admin = Role::where('name','=','admin')->first();
	    $user->attachRole($admin);

// hoặc sử dụng kĩ thuật eloquent's 
$user->roles()->attach($admin->id); // chỉ cần id
```

Bây giờ chúng ta chỉ cần gán quyền cho các user có vai trò phù hợp `Roles`.

```php
$createPost = new Permission();
$createPost->name         = 'create-post';
$createPost->display_name = 'Create Posts'; // optional
// Cho phép người dùng ...
$createPost->description  = 'tạo bài viết blog mới'; // optional
$createPost->save();

$editUser = new Permission();
$editUser->name         = 'edit-user';
$editUser->display_name = 'Edit Users'; // optional
// Cho phép người dùng...
$editUser->description  = 'chỉnh sửa người dùng hiện tại'; // optional
$editUser->save();

// áp dụng quyền cho admin
$admin->attachPermission($createPost);
// equivalent to $admin->perms()->sync(array($createPost->id));

// áp dụng quyền 1 mảng quyền hạn cho người chủ nhận
$owner->attachPermissions(array($createPost, $editUser));
// equivalent to $owner->perms()->sync(array($createPost->id, $editUser->id));
```


// như vậy là vai trò được gán cho user và user sẽ có các quyền.... 

#### Checking for Roles & Permissions

Kiểm tra vai trò của user và quyền hạn

Bây giờ chúng ta có thể kiểm tra đơn giản bằng cách làm :

```php
$user->hasRole('owner');   // false
$user->hasRole('admin');   // true
$user->can('edit-user');   // false
$user->can('create-post'); // true
```

Cả hai  `hasRole()` và  `can()` có thể nhận được một mảng vai trò và quyền hạn của vai trò để kiểm tra :

```php
// trả về user có vai trò nếu đúng
$user->hasRole(['owner', 'admin']);       // true

// trả về user có quyền hạn nếu đúng
$user->can(['edit-user', 'create-post']); // true
```
Theo mặc định , nếu có những vai trò hay quyền được áp dụng cho người sử dụng thì các phương thức này sẽ trả lại kết quả true.

Đi qua tham số thứ 2 với true để chỉ định cho phương pháp phủ định nếu có các quyền và vai trò phù hợp.

```php
$user->hasRole(['owner', 'admin']);             // true
$user->hasRole(['owner', 'admin'], true);       // false, người dùng không có vai trò admin
$user->can(['edit-user', 'create-post']);       // true
$user->can(['edit-user', 'create-post'], true); // false, người sử dụng không có quyền edit-user
```

Bạn có thể có nhiều vai trò `Role` cho mỗi `User` và ngược lại. 


Các class  `Entrust` có phím tắt facede để kiểm tra cho quyền  `can()` và vai trò phù hợp `hasRole()` khi user đã đăng nhập sử dụng.

```php
Entrust::hasRole('role-name');
Entrust::can('permission-name');

// Điều này giống như kiểm  tra xác thực user có vai trò và quyền ... 

Auth::user()->hasRole('role-name');
Auth::user()->can('permission-name);
```

Bạn có thể sử dụng "kí hiệu" cho phép kiểm tra tất cả vai trò hoặc quyền hạn của user bằng  kí tự thay thế  :

```php
// phù hợp tất cả admin quyền 
$user->can("admin.*"); // true



// phù hợp với tất cả quyền làm việc với users như thêm chỉnh sửa xóa
$user->can("*_users"); // true
```


#### User ability

định nghĩa quyền hạn của người dùng

Nhiều kiểm tra sử dụng tiên tiến phương pháp  function 'ability` so sánh một phù hợp với 1 quyền hạn "khả năng". xảy ra return true hoặc false.

Nó cần có 3 tham số trong để làm việc (roles, permissions, options):

- `roles` là một tập các vai trò cần kiểm tra
- `permissions`là một bộ quyền hạn cần kiểm tra

Vai trò và quyền có thể được tách nhau bằng dấu phẩy trong 1 mảng. 

ví dụ : 

```php
$user->ability(array('admin', 'owner'), array('create-post', 'edit-user'));

// hoặc

$user->ability('admin,owner', 'create-post,edit-user');
```

mình sẽ kiểm tra xem người dùng có bất kỳ vai trò và quyền cung cấp sau :
Trong trường hợp này, nó sẽ return true khi  user là một `admin` và có quyền `create-post`.

Tham số thứ ba là một mảng tùy chọn :

```php
$options = array(
    'validate_all' => true | false (Default: false),
    'return_type'  => boolean | array | both (Default: boolean)
);
```

- `validate_all` là một thiết lập yêu cầu kiểm tra tất cả các giá trị ít nhất 1 giá trị trả về đúng sẽ có kết quả đúng. mặc định là false.
 
- `return_type` Chỉ rõ kiểu kết quả trả về là bloolean hoặc một mảng hoặc cả 2 mặc định là boolean

Dưới đây là một ví dụ :

```php
$options = array(
    'validate_all' => true,
    'return_type' => 'both'
);

list($validate, $allValidations) = $user->ability(
    array('admin', 'owner'),
    array('create-post', 'edit-user'),
    $options
);

var_dump($validate);
// bool(false)

var_dump($allValidations);
// array(4) {
//     ['role'] => bool(true)
//     ['role_2'] => bool(false)
//     ['create-post'] => bool(true)
//     ['edit-user'] => bool(false)
// }

```
Các class  `Entrust` có một orm phương pháp  `ability()` người sử dụng hiện đăng đăng nhập :

```php
Entrust::ability('admin,owner', 'create-post,edit-user');

// giống hệt

Auth::user()->ability('admin,owner', 'create-post,edit-user');
```

### Blade templates

Các chỉ thỉ sẵn có để kiểm tra trong các file lade templates 

Three directives are available for use within your Blade templates. Những gì bạn cần là thêm  các đối số chỉ thị sẽ được đăng trực tiếp đến tham số  `Entrust` function.

```php
@role('admin')
    <p>Điều này là hiển thị cho người sử dụng với vai trò admin. Được dịch sang 
    \Entrust::role('admin')</p>
@endrole

@permission('manage-admins')
    <p>Điều này là hiển thị cho người sử dụng với quyền hạn nhất định. Được dịch sang 
    \Entrust::can('manage-admins'). Chỉ thị @can đã được dùng bởi lõi
       laravel phép gói, do đó chỉ thị @permission thay thế</p>
@endpermission

@ability('admin,owner', 'create-post,edit-user')
    <p>Điều này là hiển thị cho người sử dụng với các khả năng nhất định. Được dịch sang 
    \Entrust::ability('admin,owner', 'create-post,edit-user')</p>
@endability
```

### Middleware

Bạn có thể sử dụng một trung gian để lọc các tuyến đường và các nhóm đường bởi quyền hạn hoặc vai trò

của người sử dụng phù hợp :

```php
Route::group(['prefix' => 'admin', 'middleware' => ['role:admin']], function() {
    Route::get('/', 'AdminController@welcome');
    Route::get('/manage', ['middleware' => ['permission:manage-admins'], 'uses' => 'AdminController@manageAdmins']);
});
```

có thể sử dụng các kí hiệu | để ám chỉ or hoặc.
```php
'middleware' => ['role:admin|root']
```

Để sử dụng bộ lọc  *AND* với nhiều quyền hạn cần kiểm tra phân luồng :

```php
'middleware' => ['permission:owner', 'permission:writer']
```

Đối với các tình huống phức tạp hơn sử dụng `ability` middleware mà chấp nhận 3 tham số: roles, permissions,  validate_all
```php
'middleware' => ['ability:admin|owner,create-post|edit-user,true']
```

### Short syntax route filter

Để lọc một tuyến đường bởi sự cho phép hoặc vai trò bạn có thể gọi   `app/Http/routes.php`:

```php
// áp dụng với user có quyền  'manage_posts' có thể truy cập vào bất cứ url :  admin/post

Entrust::routeNeedsPermission('admin/post*', 'create-post');

// Áp dụng với user có vai trò là chủ nhân owner được phép truy cập url :  admin/advanced
Entrust::routeNeedsRole('admin/advanced*', 'owner');

// áp dụng 2 trong 1 tùy chọn tham số thứ hai có thể là một mảng của quyền hoặc các vai trò
// Người dùng sẽ cần phải phù hợp với tất cả các vai trò hay quyền để truy cập tuyến đường đó :

Entrust::routeNeedsPermission('admin/post*', array('create-post', 'edit-comment'));
Entrust::routeNeedsRole('admin/advanced*', array('owner','writer'));
```

Cả hai phương pháp chấp nhận một tham số thứ ba.
Nếu tham số thứ ba là null thì sự trở lại của một truy cập bị cấm. 
một kết quả trả về là thông báo lỗi   `App::abort(403)`, nếu có tham số thứ 3 sẽ được trả lại lên bạn có thể áp dụng 
nó để chuyển hướng nếu user không có quyền Redirect::to('/home' : 

```php
Entrust::routeNeedsRole('admin/advanced*', 'owner', Redirect::to('/home'));
```

Cả hai phương pháp cũng áp dụng một tham số thứ 4.
Nó mặc định là true và kiểm tra tất cả các vai trò / quyền nhất định. 

Bạn có thể đặt nó là false. Các phương pháp sẽ áp dụng các chức năng sẽ chỉ thất bại return false khi tất cả các , the function will roles/permissions vai trò và quyền hạn không có người dùng đó.

Hữu ích cho các ứng dụng quản trị mà bạn muốn cho phép truy cập cho nhóm groups.

```php
// nếu một user có  quyền 'create-post', 'edit-comment', một trong hai hoặc cả 2 họ sẽ có quyền truy cập url. 

Entrust::routeNeedsPermission('admin/post*', array('create-post', 'edit-comment'), null, false);

// nếu người dùng có vai trò là một trong hai 'owner', 'writer',hoặc cả hai họ sẽ có quyền truy cập tuyến đường url.

Entrust::routeNeedsRole('admin/advanced*', array('owner','writer'), null, false);

// nếu người dùng có vai trò là một hoặc cả  2 nếu đúng là  'owner', 'writer', hoặc  user có 1 trong các quyền hạn phù hợp 'create-post', 'edit-comment' thì họ có quyền truy cập url.


// nếu tham số thứ 4 là true thì người dùng user phải có vai trò và cả quyền hạn đúng với tất cả mới được truy cập url : 
Entrust::routeNeedsRoleOrPermission(
    'admin/advanced*',
    array('owner', 'writer'),
    array('create-post', 'edit-comment'),
    null,
    false
);
```

### Route filter

Entrust roles/permissions có thể được sử dụng trong bộ lọc bằng cách đơn giản sử dụng method Facade `can` và  `hasRole`:

```php
Route::filter('manage_posts', function()
{
    // kiểm tra người sử dụng hiện tại
    if (!Entrust::can('create-post')) {
        return Redirect::to('admin');
    }
});

// khi nào user có quyền hạn 'manage_posts' mới có thể truy cập tuyến đường admin/post
Route::when('admin/post*', 'manage_posts');
```

Sử dụng một bộ lọc kiểm tra một vai trò `role`:

```php
Route::filter('owner_role', function()
{
    // kiểm tra người sử dụng hiện tại
    if (!Entrust::hasRole('Owner')) {
        App::abort(403);
    }
});

// chỉ  owners  sẽ có quyền truy cập đến tất cả các tuyến đường admin/advanced
Route::when('admin/advanced*', 'owner_role');
```

Bạn có thể thấy  `Entrust::hasRole()` và  `Entrust::can()` kiểm tra nếu người dùng đã logged in, và sau đó nếu người đó có vai trò `role` hoặc  `permission`.

Nếu người dùng không đăng nhập tất cả trở lại sẽ là  `false`.

## Troubleshooting

# Xử lý lỗi 
Nếu bạn gặp một lỗi khi thực hiện việc chuyển đổi :

```
SQLSTATE[HY000]: General error: 1005 Can't create table 'laravelbootstrapstarter.#sql-42c_f8' (errno: 150)
    (SQL: alter table `role_user` add constraint role_user_user_id_foreign foreign key (`user_id`)
    references `users` (`id`)) (Bindings: array ())
```
Sau đó, nó có khả năng rằng các `id` trong bảng user của bạn không phù hợp với các `user_id` cột trong `role_user`
Hãy chắc chắn rằng cả hai đều là `INT(10)`.

Khi cố gắng sử dụng các phương pháp Entrust UserTrait, bạn gặp các lỗi mà trông giống như

    Class name must be a valid object or a string

có thể bạn không có công bố tài sản Entrust hoặc một cái gì đó đã đi sai khi bạn php artisan vendor:publish đã làm điều đó.
Trước hết kiểm tra xem bạn có `entrust.php` file trong thư mục `app/config`
Nếu bạn không thấy nó, sau đó thử `php artisan vendor:publish` và , nếu nó không xuất hiện, tự sao chép  `/vendor/zizaco/entrust/src/config/config.php` tập tin trong thư mục config của bạn và đổi tên nó thành `entrust.php`.

## License

Entrust là phần mềm miễn phí được phân phối theo các điều khoản của giấy phép MIT.

## Contribution guidelines

Hỗ trợ sau PSR-1 và PSR-4 PHP tiêu chuẩn mã hóa, và phiên bản ngữ nghĩa.

Hãy báo cáo bất kỳ vấn đề bạn tìm thấy trong các trang vấn đề.
yêu cầu kéo được chào đón.

<a name="fixError"></a>
### fixError 

.env file thay đổi 

	`CACHE_DRIVER=file thành CACHE_DRIVER=array`

config/auth.php thay đổi providers key thành

	'providers' => [ 
		'users' =>
		[ 'driver' => 'eloquent',
		'model' => App\User::class,
		'table' => 'users', ], 
	],

Sửa lỗi : 

	`FatalErrorException in Model.php line 956:
	Class name must be a valid object or a string`

Thay đổi `vendor\Hoanghiep\Role\src\Entrust\Traits\EntrustRoleTrait` dòng 51 thành 

`return $this->belongsToMany(Config::get('auth.providers.users.model'), Config::get('entrust.role_user_table'),Config::get('entrust.role_foreign_key'),Config::get('entrust.user_foreign_key'));`

dòng 67 trong `vendor\Hoanghiep\Role\src\Entrust\Traits\EntrustUserTrait`

		`Config::get('auth.model')`

	thay bằng
	
		 static::deleting(function($user) {
			            if (!method_exists(Config::get('auth.providers.users.model'), 'bootSoftDeletes')) {
			                $user->roles()->sync([]);
			            }
	            return true;
	        });



Sửa lỗi : Class 'App\Permission' not found

vào config/entrust.php sửa 

'role' => bằng nơi chứa file model role của bạn ví dụ của mình để role ở App\Models\Admin\Role 

thì mình sửa App\Role thành App\Models\Admin\Role

`permission` thay đổi tương tự App\Permission thàn nơi chứa model của bạn App\Models\Admin\Permission


Sửa lỗi : This cache store does not support tagging.

vào file .env sửa `CACHE_DRIVER = file` thành `CACHE_DRIVER=array`

Sửa lỗi : Method hasRole không được tìm thấy 

Call to undefined method Illuminate\Database\Query\Builder::hasRole()

Vào trong App\User nếu bạn cấu hình auth.php là model App\User nếu khác thì vào nơi chứa Class User đó thêm vào cấu trúc ;

	use Illuminate\Foundation\Auth\User as Authenticatable;
	use Hoanghiep\Role\Traits\EntrustUserTrait;

	class User extends Authenticatable
	{
	    
	      use EntrustUserTrait;


Sửa lỗi :

HttpException in Application.php line 905:

Không tìm thấy file hiển thị thông báo lỗi :

Tạo một file 403.blade.php trong views errors để thông báo lỗi 

nếu muốn họ quay lại có thể sử dụng thẻ a với nội dung 

`	<a href="{{ URL::previous() }}"> Trở lại </a>`



 
