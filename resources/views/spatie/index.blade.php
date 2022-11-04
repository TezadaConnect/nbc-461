<x-app-layout>
    @section('title', 'spatie |')


<!DOCTYPE html>
    <style>
    h2 {color:black;}
    a {color:blue;}

    .button {
    background-color: #3490dc;
    border: none;
    margin: 0;
    position: relative;
    left: 110px;
    color: white;
    padding: 8px 15px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 15px;
    margin: 10px 5px;
    cursor: pointer;
    }
    .center {

    align-items: center;
    }

    </style>
    <body>
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h2 class="font-weight-bold mb-2"></h2>
                </div>
                <div class="col-md-200">
                    <div class="card">
                        <div class="card-body">
                            <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                                    <button class="button" aria-selected="false">Download</button>
                            </ul>


                            <div class="card">
                                <div class="card-body">
                                    <p class="card-title" style="font-size:25px">Frequency of Download</p>
                                    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist"></ul>
                                    <form>
                                        <div class = "text-center">
                                            <input class="radio-input" type="radio" name="test" value="yes" />
                                            <label class="radio-label">Daily</label>
                                            <input class="radio-input" type="radio" name="test" value="yes" />
                                            <label class="radio-label">Weekly</label>
                                            <input class="radio-input" type="radio" name="test" value="yes" />
                                            <label class="radio-label">Monthly</label>
                                            <input style = "color:#f5f0ec; margin:10px; background-color:#3490dc; 
                                            border:none; border-color:none" type="submit" value="Submit">
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>


</x-app-layout>
