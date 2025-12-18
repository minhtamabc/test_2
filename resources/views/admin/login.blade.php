<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        body *{
            box-sizing: border-box;
        }
        .login-box {
            margin: auto;
            background: #fff;
            padding: 25px;
            width: 320px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .login-box h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            color: #333;
            font-size: 15px;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 15px;
        }

        button {
            width: 100%;
            padding: 10px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: #0056b3;
        }
        h2{
            margin: 0;
        }
        h3{
            text-align:center;
        }
    </style>
</head>
<body>

<div class="login-box">
    <h2>Đăng nhập</h2>
    @if(session('error'))
        <h3>Đăng nhập thất bại</h3>
    @elseif(session('success'))
        <h3>Đăng xuất thành công</h3>
    @endif
    <form action="{{ route('admin.handleLogin') }}" method="post">
        @csrf
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" placeholder="Nhập username">
        </div>

        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Nhập password">
        </div>

        <button type="submit">Đăng nhập</button>
    </form>
</div>

</body>
</html>
