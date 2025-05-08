window.onload = function(){
    document.getElementsByClassName('loader')[0].classList.add('hidden');
    document.getElementById('csv').removeAttribute('disabled');
    document.getElementById('csv').classList.remove('hidden');

    document.getElementById('csv').addEventListener('change',e=>{
        if(e.target.files && e.target.files.length ){
            Swal.fire({
                icon        :   'question',
                title       :   'Confirm Action',
                text        :   'Upload file?',
                showCancelButton    :   true,
                confirmButtonText   :   'Confirm'
            }).then(value=>{
                document.getElementById('csv').setAttribute('disabled',true);
                document.getElementById('csv').classList.add('hidden');
                document.getElementsByClassName('loader')[0].classList.remove('hidden');
                document.getElementsByClassName('results')[0].classList.remove('hidden');
                if(value.isConfirmed){
                    Papa.parse(e.target.files[0], {
                        header          :   true,
                        complete        :   function(result,file){
                            let retry_button = document.createElement('button');
                            retry_button.textContent = 'Upload Again';
                            retry_button.setAttribute('onclick','location.reload()');
                            retry_button.classList.add('button');
                            retry_button.setAttribute('style','margin-top: 10px;');
                            document.getElementsByClassName('results')[0].appendChild(retry_button)
                            Swal.fire({
                                icon    :   'success',
                                title   :   'Success',
                                text    :   'Upload Complete!'
                            });                            
                        document.getElementsByClassName('loader')[0].classList.add('hidden');
                        }, 
                        skipEmptyLines  :   true,
                        step            :   function(results,parser){
                            parser.pause();
                            if( results.errors.length ){
                                console.log("Row errors:", results.errors);
                                parser.resume();
                            }

                            if(results.data){
                                const data = new FormData();
                                data.append('action','process_record');
                                data.append('coupon_code',results.data['Code']);
                                data.append('product_ids',results.data['Product IDs']);
                                data.append('expiry_date',results.data['Expiry date']);
                                data.append('excerpt',results.data['Description']);
                                data.append('usage_limit',results.data['Usage / Limit']);
                                data.append('coupon_amount',results.data['Coupon Amount']);
                                let coupon_type = 'fixed_cart';
                                if(results.data['Coupon Type'] === 'Percentage discount'){
                                    coupon_type = 'percent';
                                } else if(results.data['Coupon Type'] === 'Fixed product discount'){
                                    coupon_type = 'fixed_product';
                                } else if(results.data['Coupon Type'] === 'Multi-purpose voucher'){
                                    coupon_type = 'multi_purpose_voucher';
                                }

                                data.append('discount_type',coupon_type);

                                fetch(WCBCU.ajaxURL,{
                                    body        :   data,
                                    credentials :   'same-origin',
                                    method      :   'POST'
                                }).then(response=> response.json()
                                ).then(result=>{
                                    let td1 = document.createElement('td');
                                    td1.textContent = results.data['Code'];
                                    let td2 = td1.cloneNode();
                                    td2.classList.add('coupon-code-creation');
                                    if(result.success){
                                        td2.innerHTML = `Coupon Created [ <a href="${WCBCU.site_url}/wp-admin/post.php?post=${result.couponId}&action=edit" target="_blank">View</a> ]`;
                                        td2.classList.add('success');
                                    } else {
                                        td2.textContent = result.message;
                                        td2.classList.add('failed');
                                    }               
                                    let tr = document.createElement('tr');
                                    tr.appendChild(td1);
                                    tr.appendChild(td2);
                                    document.getElementById('results-table').appendChild(tr);

                            
                                    parser.resume();
                                })
                            }
                        }
                    });
                }
            });
        }
    })
}