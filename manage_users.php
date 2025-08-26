<?php
require_once 'includes/auth_check.php';
if (!isAdmin()) {
    header("Location: /index.php");
    exit();
}
?>

<div class="container-fluid">
    <h1 class="mt-4">Manage Users</h1>
    <button class="btn btn-primary mb-3" id="addUserBtn">Add User</button>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="usersTableBody">
            <!-- User data will be loaded here -->
        </tbody>
    </table>
</div>

<!-- User Modal -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalLabel">Add User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="userForm">
                    <input type="hidden" id="userId">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password">
                        <small class="form-text text-muted">Leave blank to keep current password.</small>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" id="role" required>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    function loadUsers() {
        $.get('/api/get_users.php', function(response) {
            if (response.success) {
                let usersHtml = '';
                response.users.forEach(user => {
                    usersHtml += `
                        <tr>
                            <td>${user.id}</td>
                            <td>${user.username}</td>
                            <td>${user.role}</td>
                            <td>
                                <button class="btn btn-sm btn-info edit-user" data-id="${user.id}">Edit</button>
                                <button class="btn btn-sm btn-danger delete-user" data-id="${user.id}">Delete</button>
                            </td>
                        </tr>
                    `;
                });
                $('#usersTableBody').html(usersHtml);
            }
        });
    }

    $('#addUserBtn').on('click', function() {
        $('#userForm')[0].reset();
        $('#userId').val('');
        $('#userModalLabel').text('Add User');
        $('#userModal').modal('show');
    });

    $('#usersTableBody').on('click', '.edit-user', function() {
        const userId = $(this).data('id');
        $.get('/api/get_user.php', { id: userId }, function(response) {
            if (response.success) {
                const user = response.user;
                $('#userId').val(user.id);
                $('#username').val(user.username);
                $('#role').val(user.role);
                $('#userModalLabel').text('Edit User');
                $('#userModal').modal('show');
            }
        });
    });

    $('#userForm').on('submit', function(e) {
        e.preventDefault();
        const userData = {
            id: $('#userId').val(),
            username: $('#username').val(),
            password: $('#password').val(),
            role: $('#role').val()
        };

        const url = userData.id ? '/api/update_user.php' : '/api/add_user.php';

        $.post(url, userData, function(response) {
            if (response.success) {
                $('#userModal').modal('hide');
                loadUsers();
                showToast(response.message, 'success');
            } else {
                showToast(response.message, 'error');
            }
        });
    });

    $('#usersTableBody').on('click', '.delete-user', function() {
        const userId = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('/api/delete_user.php', { id: userId }, function(response) {
                    if (response.success) {
                        loadUsers();
                        Swal.fire('Deleted!', 'The user has been deleted.', 'success');
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                });
            }
        });
    });

    loadUsers();
});
</script>
