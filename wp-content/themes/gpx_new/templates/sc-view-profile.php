<section class="w-banner w-results w-results-home w-profile">
    <ul id="slider-home" class="royalSlider heroSlider rsMinW rsFullScreen rsFullScreen-result rsviewprofile">
        <li class="slider-item rsContent">
            <img class="rsImg" src="<?php echo get_template_directory_uri(); ?>/images/bg-result.jpg" alt=""/>
        </li>
    </ul>
    <div class="w-options">
        <hgroup>
            <h1>
                <div><?= $usermeta->last_name ?>, <?= $usermeta->first_name ?></div>
            </h1>
        </hgroup>
        <div class="p">
            <p>Deposits: <strong><span id="creditBal"></span></strong></p>
        </div>
    </div>
</section>
<?php include( locate_template( 'template-parts/universal-search-widget.php' ) ); ?>
<dialog id="modal-profile" data-width="800" data-height="500" data-close-on-outside-click="false">
    <div class="w-modal">
        <div class="member-form">
            <div class="w-form">
                <form action="" class="material" method="post">
                    <input type="hidden" name="cid" value="<?php esc_attr_e($cid) ?>">
                    <?php foreach ( $profilecols as $pcKey => $col ): ?>
                        <ul class="list-form">
                            <?php foreach ( $col as $data ): ?>
                                <?php
                                //set the variables for the value
                                $value    = '';
                                $fromvar  = $data['value']['from'];
                                $from     = $$fromvar;
                                $retrieve = $data['value']['retrieve'];
                                if ( isset( $from->$retrieve ) ) {
                                    $value = $from->$retrieve;
                                }
                                ?>
                                <li>
                                    <div class="ginput_container">
                                        <input type="text" placeholder="<?= $data['placeholder'] ?>"
                                               name="<?= $retrieve ?>" class="<?= $data['class'] ?>"
                                               value="<?= $value; ?>" <?= $data['required'] ?>>
                                    </div>
                                </li>
                                <?php if ( $pcKey == '1' && end( $col ) == $data ): ?>
                                    <li>
                                        <input class="edit-profile-btn dgt-btn" type="submit" value="Update">
                                    </li>
                                <?php endif; ?>

                            <?php endforeach; ?>
                        </ul>
                    <?php endforeach; ?>
                </form>
            </div>
        </div>
    </div>
</dialog>


<section class="bg-gray-light view-profile">
    <div class="dgt-container tabbed">
        <ul class="tab-menu-items">
            <li class="active tab-menu-item"><a href="#my-profile">My Profile Info</a></li>
            <li class="tab-menu-item"><a href="#password-profile">Password Management</a></li>
            <li class="tab-menu-item"><a href="#ownership-profile">Ownership Weeks</a></li>
            <li class="tab-menu-item"><a href="#weeks-profile">Weeks Deposited</a></li>
            <li class="tab-menu-item"><a href="#history-profile">Transaction History</a></li>
            <li class="tab-menu-item"><a href="#holdweeks-profile">My Held Weeks</a></li>
            <?php
            if ( isset( $mycoupons ) && ! empty( $mycoupons ) ) {
                ?>
                <li class="tab-menu-item"><a href="#myCoupons">My Coupons</a></li>
                <?php
            }
            if ( isset( $mycreditcoupons ) && ! empty( $mycreditcoupons ) ) {
                ?>
                <li class="tab-menu-item"><a href="#myCreditCoupons">My Credit Coupons</a></li>
                <?php
            }
            ?>
            <li class="tab-menu-item"><a href="#customrequest-profile">Special Requests</a></li>
            <li class="tab-menu-item"><a href="#search-profile">Search History</a></li>
        </ul>
        <div id="my-profile" class="w-information active">
            <div class="title">
                <h4>My Profile Information</h4>
                <?php
                if ( ! isset( $usermeta->Mobile ) ) {
                    $usermeta->Mobile = '';
                }
                ?>
                <div class="title-close">
                    <a href="<?php echo site_url(); ?>/member-dashboard">
                        <p>Close and Return To Member Dashboard</p>
                        <i class="icon-close"></i>
                    </a>
                </div>
            </div>
            <div class="content">
                <ul>
                    <li>
                        <p><strong>Member Name</strong></p>
                        <p><?= $usermeta->first_name ?> <?= $usermeta->last_name ?></p>
                    </li>
                    <li>
                        <p><strong>Member Number</strong></p>
                        <p><?= $gprOwner->user_id ?></p>
                    </li>
                    <li>
                        <p><strong>Email</strong></p>
                        <?php
                        if ( empty( $usermeta->Email ) ) {
                            $usermeta->Email = $usermeta->email;
                            if ( empty( $usermeta->Email ) ) {
                                $usermeta->Email = $usermeta->user_email;
                            }
                        }
                        $email = $usermeta->Email;
                        //             			if(empty($email))
                        //             			{
                        //             			    $email = $usermeta->email;
                        //             			}
                        ?>
                        <p><?= $email ?></p>
                    </li>
                    <li>
                        <p><strong>Home Phone</strong></p>
                        <p><?= $user->DayPhone ?></p>
                    </li>
                    <li>
                        <p><strong>Mobile Phone</strong></p>
                        <p><?= $usermeta->Mobile ?></p>
                    </li>
                    <li>
                        <p><strong>Street Address</strong></p>
                        <p><?= $usermeta->Address1 ?></p>
                    </li>
                    <li>
                        <p><strong>City</strong></p>
                        <p><?= $usermeta->Address3 ?></p>
                    </li>
                    <li>
                        <p><strong>State</strong></p>
                        <p><?= $usermeta->Address4 ?></p>
                    </li>
                    <li>
                        <p><strong>Zip Code</strong></p>
                        <p><?= $usermeta->PostCode ?></p>
                    </li>
                    <?php
                    if ( isset( $usermeta->OnAccountAmount ) && ( ! empty( $usermeta->OnAccountAmount ) && $usermeta->OnAccountAmount < 0 ) )
                    {
                    ?>
                    <li>

                        <br><br><br>
                        <strong>If changes are needed to your information above please contact a GPX Representative at
                            866-325-6295.</strong>

                        <?php
                        }
                        ?>
                </ul>
            </div>
        </div>
        <div id="password-profile" class="w-information">
            <div class="title">
                <h4>Password Management</h4>
            </div>
            <div class="content">
                <?php
                if ( $cid != get_current_user_id() ) {
                    ?>
                    <div>
                        <a href="" class="password-reset-link" data-userlogin="<?= $user->user_login ?>">Email Password
                            Reset Link</a>
                    </div>
                    <div id="vp-pw-alert-msg"></div>
                    <?php
                }
                ?>
                <div class="form">
                    <form action="" id="newpwform" class="material" data-cid="<?= $cid ?>">
                        <?php
                        if ( $cid == get_current_user_id() ) {
                            ?>
                            <div class="gpinput">
                                <input type="password" placeholder="Type your old password" class="successclear"
                                       name="hash" autocomplete="off" required>
                                <a href="/wp-login.php?action=lostpassword" target="_blank">forgot password?</a>
                            </div>
                            <?php
                        } else {
                            ?>
                            <div class="gpinput">
                                <h4>Manually Reset Password</h4>
                            </div>
                            <?php
                        }
                        ?>
                        <div class="gpinput">
                            <input type="password" id="chPassword" name="chPassword" class="successclear"
                                   placeholder="Type new password" autocomplete="off" required>
                        </div>
                        <div class="gpinput">
                            <input type="password" id="chPasswordConfirm" class="successclear" name="chPasswordConfirm"
                                   placeholder="Confirm new password" autocomplete="off" required>
                        </div>
                        <div class="gpinput">
                            <input type="submit" id="pwChange" class="dgt-btn" value="save">
                        </div>
                        <div class="gpinput">
                            <p class="pwMsg"></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div id="ownership-profile" class="w-information table-responsive">
            <div class="title">
                <h4>Ownership Weeks</h4>
            </div>
            <div class="content content-table" data-id="<?= $cid ?>">
                <div class="loading">
                    <i class="fa fa-refresh fa-spin fa-3x fa-fw"></i>
                    <span class="sr-only">Loading...</span>
                </div>
                <table class="ajax-data-table" id="ownership"></table>
                <div class="pagination">
                    <div class="cnt">
                        <div>
                            <div class="arrow icon-arrow-left"></div>
                            <div class="number">1</div>
                            <div class="arrow icon-arrow-right"></div>
                        </div>
                        <div>
                            of <span>25</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="weeks-profile" class="w-information">
            <div class="title">
                <h4>Weeks Deposited</h4>
            </div>
            <div class="content content-table" data-id="<?= $cid ?>">
                <div class="loading">
                    <i class="fa fa-refresh fa-spin fa-3x fa-fw"></i>
                    <span class="sr-only">Loading...</span>
                </div>
                <h4>Available</h4>
                <table class="ajax-data-table" id="deposit">

                </table>
                <h4 style="margin-top: 60px;">Unavailable</h4>
                <table class="ajax-data-table" id="depositused">

                </table>
            </div>
        </div>
        <div id="history-profile" class="w-information">
            <div class="title">
                <h4>My Transaction History</h4>
            </div>
            <div class="content content-table transaction-load" data-load="load_transactions" data-id="<?= $cid ?>"
                 data-type="transactions">
                <div class="loading">
                    <i class="fa fa-refresh fa-spin fa-3x fa-fw"></i>
                    <span class="sr-only">Loading...</span>
                </div>
                <div>
                    <h4>Exchange Weeks</h4>
                    <table class="ajax-data-table" id="exchange">
                    </table>
                    <div class="pagination">
                        <div class="cnt">
                            <div>
                                <div class="arrow icon-arrow-left"></div>
                                <div class="number">1</div>
                                <div class="arrow icon-arrow-right"></div>
                            </div>
                            <div>
                                of <span>25</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <h4>Bonus / Rental Weeks</h4>
                    <table class="ajax-data-table" id="bnr">
                    </table>
                    <div class="pagination">
                        <div class="cnt">
                            <div>
                                <div class="arrow icon-arrow-left"></div>
                                <div class="number">1</div>
                                <div class="arrow icon-arrow-right"></div>
                            </div>
                            <div>
                                of <span>25</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <h4>Miscellaneous</h4>
                    <table class="ajax-data-table" id="misc">
                    </table>
                    <div class="pagination">
                        <div class="cnt">
                            <div>
                                <div class="arrow icon-arrow-left"></div>
                                <div class="number">1</div>
                                <div class="arrow icon-arrow-right"></div>
                            </div>
                            <div>
                                of <span>25</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        if ( isset( $mycoupons ) && ! empty( $mycoupons ) ) {
            ?>
            <div id="myCoupons" class="w-information">
                <div class="title">
                    <h4>My Coupons</h4>
                </div>
                <div class="content content-table">
                    <table class="data-table">
                        <thead>
                        <tr>
                            <td>Offer Link</td>
                            <td>Coupon Code</td>
                            <td>Details</td>
                            <td>Redeemed</td>
                            <td></td>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach ( $mycoupons as $coupon ) {
                            ?>
                            <tr>
                                <td><a href="/promotion/<?= $coupon['slug'] ?>"><?= $coupon['name'] ?> <i
                                            class="fa fa-link"></i></a></td>
                                <td style="max-width: 75px;" class="copyText"><span
                                        class="copy"><?= $coupon['code'] ?></span> <i class="fa fa-files-o"
                                                                                      aria-hidden="true"></i></td>
                                <td style="white-space: normal;"><?= $coupon['details'] ?></td>
                                <td><?= $coupon['redeemed'] ?></td>
                                <td></td>
                            </tr>
                            <?php
                        }
                        ?>
                        </tbody>
                    </table>
                    <div class="pagination">
                        <div class="cnt">
                            <div>
                                <div class="arrow icon-arrow-left"></div>
                                <div class="number">1</div>
                                <div class="arrow icon-arrow-right"></div>
                            </div>
                            <div>
                                of <span>25</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
        ?>
        <?php
        if ( isset( $mycreditcoupons ) && ! empty( $mycreditcoupons ) ) {
            ?>
            <div id="myCreditCoupons" class="w-information">
                <div class="title">
                    <h4>My Credit Coupons</h4>
                </div>
                <div class="content content-table">
                    <table class="data-table">
                        <thead>
                        <tr>
                            <td>Offer Link</td>
                            <td>Coupon Code</td>
                            <td>Balance</td>
                            <td>Redeemed</td>
                            <td>Expiration Date</td>
                            <td></td>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach ( $mycreditcoupons as $mycreditcoupon ) {
                            $activeClass = '';
                            $copy        = '<td class="copyText"><span class="copy">' . $mycreditcoupon['code'] . '</span> <i class="fa fa-files-o" aria-hidden="true"></i></td>';
                            if ( $mycreditcoupon['active'] == '0' ) {
                                $activeClass = 'cancelled-week';
                                $copy        = '<td>' . $mycreditcoupon['code'] . '</td>';
                            }
                            ?>
                            <tr class="<?= $activeClass ?>">
                                <td><?= $mycreditcoupon['name'] ?></td>
                                <?= $copy ?>
                                <td><?= $mycreditcoupon['balance'] ?></td>
                                <td><?= $mycreditcoupon['redeemed'] ?></td>
                                <td><?= $mycreditcoupon['expire'] ?></td>
                                <td></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                    <div class="pagination">
                        <div class="cnt">
                            <div>
                                <div class="arrow icon-arrow-left"></div>
                                <div class="number">1</div>
                                <div class="arrow icon-arrow-right"></div>
                            </div>
                            <div>
                                of <span>25</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
        <div id="holdweeks-profile" class="w-information">
            <div class="title">
                <h4>My Weeks on Hold</h4>
            </div>
            <div class="content content-table">
                <div class="loading">
                    <i class="fa fa-refresh fa-spin fa-3x fa-fw"></i>
                    <span class="sr-only">Loading...</span>
                </div>
                <h4>Held Weeks</h4>
                <table class="ajax-data-table" id="holdweeks"></table>
                <div class="pagination">
                    <div class="cnt">
                        <div>
                            <div class="arrow icon-arrow-left"></div>
                            <div class="number">1</div>
                            <div class="arrow icon-arrow-right"></div>
                        </div>
                        <div>
                            of <span>25</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="customrequest-profile" class="w-information">
            <div class="title">
                <h4>My Special Requests</h4>
            </div>
            <div class="content content-table">
                <h4>Special Requests</h4>
                <table class="data-table">
                    <li>Each Special Request will be followed-up with an email the first time that a match is made. When
                        a match is made, that week is <u>NOT</u> automatically placed on hold and it is available to be
                        booked by any GPX member. Therefore, we highly suggest that when a match is made that <strong>you
                            immediately Hold or Book</strong> the week.
                    </li>
                    <li>Availability is updated in real time. Check back frequently to increase your chances of booking
                        a matched Special Request.
                    </li>
                    <br>
                    <?php if ( isset( $customRequests ) ): ?>
                        <table>
                            <thead>
                            <tr>
                                <td>Region/Resort Selected</td>
                                <td style="width: 220px;">Date of Travel</td>
                                <td>Date Request Submitted</td>
                                <td style="width: 175px;">Matched Weeks</td>
                                <td style="width: 80px;">Active</td>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ( $customRequests as $cr ): ?>
                                <tr>
                                    <td><?= $cr['location'] ?></td>
                                    <td><?= $cr['traveldate'] ?></td>
                                    <td><?= $cr['requesteddate'] ?></td>
                                    <td><?= $cr['matched'] ?></td>
                                    <td><?= $cr['active'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>

                        </table>
                    <?php endif; ?>
                    <div class="pagination">
                        <div class="cnt">
                            <div>
                                <div class="arrow icon-arrow-left"></div>
                                <div class="number">1</div>
                                <div class="arrow icon-arrow-right"></div>
                            </div>
                            <div>
                                of <span>25</span>
                            </div>
                        </div>
                    </div>
            </div>
        </div>

        <div id="search-profile" class="w-information">
            <div class="title">
                <h4>My Search History</h4>
            </div>
            <div class="content content-table">
                <div>
                    <?php if ( isset( $histoutresort ) ): ?>
                        <h4>Resorts</h4>
                        <table class="data-table">
                            <thead>
                            <tr>
                                <td>Resort Name</td>
                                <td>Date Viewed</td>
                                <td></td>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ( $histoutresort as $histprop ): ?>
                                <tr>
                                    <td><?= $histprop['ResortName'] ?></td>
                                    <td><?= $histprop['DateViewed'] ?></td>
                                    <td></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div class="pagination">
                            <div class="cnt">
                                <div>
                                    <div class="arrow icon-arrow-left"></div>
                                    <div class="number">1</div>
                                    <div class="arrow icon-arrow-right"></div>
                                </div>
                                <div> of <span>25</span></div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="dgt-container g-w-modal">
        <div class="dialog dialog--opaque" id="modal-transaction" data-width="1000" data-close-on-outside-click="false">
            <div class="w-modal">
                <div class="modal-body" id="transaction-details"></div>
            </div>
        </div>
    </div>
</section>
