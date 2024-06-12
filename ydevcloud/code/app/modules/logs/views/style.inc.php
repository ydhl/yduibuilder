
<style>
    .log-container{
        margin: 15px;
        padding: 5px;
        background-color: #fff;
    }
    .log-body{
        overflow: auto;
    }
    .log-container table {
        width: 100%;
        color: #212529;
    }

    .log-container table th,
    .log-container table td {
        padding: 0.75rem;
        vertical-align: top;
        border-top: 1px solid #dee2e6;
    }

    .log-container table thead th {
        vertical-align: bottom;
        border-bottom: 2px solid #dee2e6;
    }

    .log-container table tbody + tbody {
        border-top: 2px solid #dee2e6;
    }
    .log-container tbody tr:hover {
        color: #212529;
        background-color: rgba(0, 0, 0, 0.075);
    }
    .log-text-truncate {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .log-delete{
        padding: 3px;background-color: #ff3636;
        border: 1px solid #b81414;
        margin-left: 35px;
        color: #fff;
    }
</style>
