<?php
/**
 * Template Name: View Profile Page
 * Theme: GPX
 */

get_header();

?> 

<!-- Indicaciones - ELiminar esta sección-->
<section class="w-banner w-results w-results-home w-profile">
    <ul id="slider-home" class="royalSlider heroSlider rsMinW rsFullScreen rsFullScreen-result rsviewprofile">
        <li class="slider-item rsContent">
            <img class="rsImg" src="<?php echo get_template_directory_uri(); ?>/images/bg-result.jpg" alt="" />
        </li>
    </ul>
    <div class="w-options">
        <hgroup>
            <h2><div>Wagner, Renee</div> | <a href=""><span>view profile</span></a></h2>
        </hgroup>
        <div class="p">
          <p>Exchange Credits: <strong>2</strong></p>
        </div>
    </div>
</section>
<section class="review bg-gray-light view-profile">
    <div class="dgt-container">
        <div class="w-information">
          <div class="title">
              <h4>My Profile Information</h4>
              <div class="title-close">
                <a href="<?php echo site_url(); ?>/result/">
                  <p>Close and Return to Dashboard</p>
                  <i class="icon-close"></i>
                </a>
              </div>
          </div>
          <div class="content">
            <ul>
              <li>
                <p><strong>Member Name</strong></p>
                <p>Wagner, Renee</p>
              </li>
              <li>
                <p><strong>Member Number</strong></p>
                <p>330418</p>
              </li>
              <li>
                <p><strong>Email</strong></p>
                <p>reneeiscool@realcoo.com</p>
              </li>
              <li>
                <p><strong>Home Phone</strong></p>
                <p>000 000 000</p>
              </li>
              <li>
                <p><strong>Mobile Phone</strong></p>
                <p>000 000 000</p>
              </li>
              <li>
                <p><strong>Street Address</strong></p>
                <p>1406 East Ridgwood Street</p>
              </li>
              <li>
                <p><strong>City</strong></p>
                <p>Orlando</p>
              </li>
              <li>
                <p><strong>State</strong></p>
                <p>Florida</p>
              </li>
              <li>
                <p><strong>Zip Code</strong></p>
                <p>32803</p>
              </li>
            </ul>
            <a href="" class="dgt-btn">Edit</a>
          </div>
        </div>
        <div class="w-information">
          <div class="title">
              <h4>Password Management</h4>
          </div>
          <div class="content">
              <div class="form">
                <form action="" class="material">
                  <div class="gpinput">
                    <input type="text" placeholder="Type your old password" required>
                    <a href="">forgot password?</a>
                  </div>
                  <div class="gpinput">
                    <input type="password" placeholder="Type new password" required >
                  </div>
                  <div class="gpinput">
                    <input type="password" placeholder="Confirm new password" required >
                  </div>
                  <div class="gpinput">
                    <input type="submit" class="dgt-btn" value="save">
                  </div>
                </form>
              </div>
          </div>
        </div>
        <div class="w-information">
          <div class="title">
              <h4>Weeks Deposited</h4>
          </div>
          <div class="content content-table">
              <table id="table1">
              <thead>
                <tr>
                  <td>Ref No.</td>
                  <td>Resort Name</td>
                  <td>Entitlement Year</td>
                  <td>Unit</td>
                  <td>Status</td>
                  <td>Balance</td>
                  <td>Expiry Used</td>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>244430</td>
                  <td>Carlsbad Seapoint Resort</td>
                  <td>2016</td>
                  <td>2/6</td>
                  <td>Banked</td>
                  <td>1</td>
                  <td>1</td>
                </tr>
              </tbody>
              </table>
          </div>
        </div>
        <div class="w-information">
          <div class="title">
            <h4>My Transaction History</h4>
          </div>
          <div class="content content-table">
            <div>
              <h4>Exchange Weeks</h4>
              <table id="table2" >
                <thead>
                  <tr>
                    <td>Ref No.</td>
                    <td>Resort Name</td>
                    <td>Guest Name</td>
                    <td>Status</td>
                    <td>Unit</td>
                    <td>Check-In</td>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>244430</td>
                    <td>Carlsbad Seapoint Resort</td>
                    <td>Hoesten, Michel</td>
                    <td>For Placement Only</td>
                    <td>2/6</td>
                    <td>27 Dec 2015</td>
                  </tr>
                  <tr>
                    <td>244430</td>
                    <td>Carlsbad Seapoint Resort</td>
                    <td>Hoesten, Michel</td>
                    <td>For Placement Only</td>
                    <td>2/6</td>
                    <td>27 Dec 2015</td>
                  </tr>
                  <tr>
                    <td>244430</td>
                    <td>Carlsbad Seapoint Resort</td>
                    <td>Hoesten, Michel</td>
                    <td>For Placement Only</td>
                    <td>2/6</td>
                    <td>27 Dec 2015</td>
                  </tr>
                  <tr>
                    <td>244430</td>
                    <td>Carlsbad Seapoint Resort</td>
                    <td>Hoesten, Michel</td>
                    <td>For Placement Only</td>
                    <td>2/6</td>
                    <td>27 Dec 2015</td>
                  </tr>
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
            <div>
              <h4>Bonus / Rental Weeks</h4>
              <table>
                <thead>
                  <tr>
                    <td>Ref No.</td>
                    <td>Resort Name</td>
                    <td>Guest Name</td>
                    <td>Status</td>
                    <td>Unit</td>
                    <td>Check-In</td>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>244430</td>
                    <td>Carlsbad Seapoint Resort</td>
                    <td>Hoesten, Michel</td>
                    <td>For Placement Only</td>
                    <td>2/6</td>
                    <td>27 Dec 2015</td>
                  </tr>
                  <tr>
                    <td>244430</td>
                    <td>Carlsbad Seapoint Resort</td>
                    <td>Hoesten, Michel</td>
                    <td>For Placement Only</td>
                    <td>2/6</td>
                    <td>27 Dec 2015</td>
                  </tr>
                  <tr>
                    <td>244430</td>
                    <td>Carlsbad Seapoint Resort</td>
                    <td>Hoesten, Michel</td>
                    <td>For Placement Only</td>
                    <td>2/6</td>
                    <td>27 Dec 2015</td>
                  </tr>
                  <tr>
                    <td>244430</td>
                    <td>Carlsbad Seapoint Resort</td>
                    <td>Hoesten, Michel</td>
                    <td>For Placement Only</td>
                    <td>2/6</td>
                    <td>27 Dec 2015</td>
                  </tr>
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
        </div>
        <div class="w-information">
          <div class="title">
            <h4>My Search History</h4>
          </div>
          <div class="content content-table">
            <div>
              <h4>Exchange Weeks</h4>
              <table>
                <thead>
                  <tr>
                    <td>Ref No.</td>
                    <td>Resort Name</td>
                    <td>Guest Name</td>
                    <td>Status</td>
                    <td>Unit</td>
                    <td>Check-In</td>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>244430</td>
                    <td>Carlsbad Seapoint Resort</td>
                    <td>Hoesten, Michel</td>
                    <td>For Placement Only</td>
                    <td>2/6</td>
                    <td>27 Dec 2015</td>
                  </tr>
                  <tr>
                    <td>244430</td>
                    <td>Carlsbad Seapoint Resort</td>
                    <td>Hoesten, Michel</td>
                    <td>For Placement Only</td>
                    <td>2/6</td>
                    <td>27 Dec 2015</td>
                  </tr>
                  <tr>
                    <td>244430</td>
                    <td>Carlsbad Seapoint Resort</td>
                    <td>Hoesten, Michel</td>
                    <td>For Placement Only</td>
                    <td>2/6</td>
                    <td>27 Dec 2015</td>
                  </tr>
                  <tr>
                    <td>244430</td>
                    <td>Carlsbad Seapoint Resort</td>
                    <td>Hoesten, Michel</td>
                    <td>For Placement Only</td>
                    <td>2/6</td>
                    <td>27 Dec 2015</td>
                  </tr>
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
        </div>
    </div>
</section>

<?php get_footer(); ?>
<script>
  $('body').addClass('active-session');
</script>