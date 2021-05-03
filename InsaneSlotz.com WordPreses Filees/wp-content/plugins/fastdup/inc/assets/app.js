new Vue({
  el: '#app',
  data: {
    zipfiles: JSON.parse('<?php echo json_encode($install_package->unzipper->zipfiles); ?>'),
    system_scan: JSON.parse('<?php echo json_encode($system_scan); ?>'),
    loading: false,
    current: 0,
    countClickSetting: 0,
    steps: [{
      key: 0,
      title: 'Requirements',
    }, {
      key: 1,
      title: 'Settings',
    }, {
      key: 2,
      title: 'Done',
    }],
    installForm: "",
    is_delete_install_files: false,
    formData: {
      db_host: "localhost",
      db_name: "",
      db_user: "",
      db_pass: "",
      zipfile: []
    },
    alertForm: {
      status: false,
      message: '',
      message_detail: ''
    }
  },
  created() {
    this.installForm = this.$form.createForm(this, {
      onFieldsChange: (_, changedFields) => {
        this.$emit("change", changedFields)
      },
    })
    this.installForm.getFieldDecorator("db_host", { initialValue: this.formData.db_host })
    this.installForm.getFieldDecorator("db_name", { initialValue: this.formData.db_name })
    this.installForm.getFieldDecorator("db_user", { initialValue: this.formData.db_user })
    this.installForm.getFieldDecorator("db_pass", { initialValue: this.formData.db_pass })
    this.installForm.getFieldDecorator("zipfile", { initialValue: this.formData.zipfile })
  },
  methods: {
    next() {
      // Step Requirements
      if(this.current == 0){
        if(this.system_scan.status){
         this.current = 1
        }else{
          this.$message.warning('Please Check All Requirements');
        }
        this.loading = false;
      }

      // Step Settings
      if(this.current == 1){
        this.countClickSetting++;
        if(this.countClickSetting > 2){
          this.countClickSetting = 2;
        }
        if(this.countClickSetting == 2){
          this.loading = true;
          this.installForm.validateFields((err, values) => {
              if (!err) {
                var data = JSON.stringify(values)
                axios.post(window.location.href, data, {
                  headers: {
                    Accept: "application/json",
                    "Content-Type": "application/json",
                  }
                }).then((response) => {
                  let data = response.data;
                  this.loading = false;
                  this.alertForm = data;

                  if(data.status == 'error'){
                    this.countClickSetting = 0
                  }else{
                    this.current++
                  }
                });
              } else {
                this.loading = false;
              }
          });
        }else{
          this.loading = false;
        }

      }
    },
    prev() {
      this.current--
      this.countClickSetting = 0
    },
    deleteInstallFiles(e){
      this.is_delete_install_files = e.target.checked
    },
    cleanInstallerFile(){
      axios.post(window.location.href, {
        type: 'clean_install_file'
      }, {
        headers: {
          Accept: "application/json",
          "Content-Type": "application/json",
        }
      });
    },
    testDatabase(){
      let formData = this.installForm.getFieldsValue()
      if(formData.db_host  && formData.db_user){
        axios.post(window.location.href, {
          type: 'test_database',
          db_host: formData.db_host,
          db_name: formData.db_name,
          db_user: formData.db_user,
          db_pass: formData.db_pass,
        }, {
          headers: {
            Accept: "application/json",
            "Content-Type": "application/json",
          }
        }).then((response) => {
          let data = response.data;
          if(data.status){
            this.$message.success(data.message);
          }else{
            this.$message.warning(data.message);
          }
        });
      }else{
        this.$message.warning('Please Fill Database Host, Database User');
      }
    },
    redirectToAdmin(){
      let currentUrl = window.location.href
      const installerFileName = currentUrl.split(/[\\/]/).pop()

      if(this.is_delete_install_files){
        this.cleanInstallerFile()
      }
      currentUrl = currentUrl.replace(installerFileName, "wp-login.php");
      location.replace(currentUrl)
    }
  }

})