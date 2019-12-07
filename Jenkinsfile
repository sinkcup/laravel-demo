def remote = [:]
remote.name = 'web-server'
remote.allowAnyHosts = true

dockerUser = ""
dockerPassword = ""

node {
    checkout([
        $class: 'GitSCM',
        branches: [[name: env.GIT_BUILD_REF]],
        userRemoteConfigs: [[url: env.GIT_REPO_URL, credentialsId: env.CREDENTIALS_ID]]
    ])
    codePath = sh(script: 'pwd', returnStdout: true).trim()
    sh 'docker network create bridge1'
    sh(script:'docker run --net bridge1 --name mysql -d -e "MYSQL_ROOT_PASSWORD=my-secret-pw" -e "MYSQL_DATABASE=test" mysql:5.7', returnStdout: true)
    sh(script:'docker run --net bridge1 --name redis -d redis:5', returnStdout: true)

    // docker build image for testing
    sh "docker login -u $DOCKER_USER -p $DOCKER_PASSWORD $DOCKER_SERVER"
    md5 = sh(script: "md5sum Dockerfile | awk '{print \$1}'", returnStdout: true).trim()
    imageName = "${DOCKER_SERVER}${DOCKER_PATH}:dev-${md5}"
    dockerNotExists = sh(script: "DOCKER_CLI_EXPERIMENTAL=enabled docker manifest inspect $imageName > /dev/null", returnStatus: true)
    def testImage = null
    if (dockerNotExists) {
        testImage = docker.build(imageName, "--build-arg APP_ENV=testing --build-arg SPEED=$SPEED ./")
        sh "docker push $imageName"
    } else {
        testImage = docker.image(imageName)
    }
    testImage.inside("--net bridge1 -v \"${codePath}:/var/www/laravel\" -e 'APP_ENV=testing' -e 'DB_DATABASE=test'" +
       " -e 'DB_USERNAME=root' -e 'DB_PASSWORD=my-secret-pw' -e 'DB_HOST=mysql' -e 'REDIS_HOST=redis'" +
        " -e 'APP_KEY=base64:tbgOBtYci7i7cdx5RiFE3KZzUkRtJfbU3lbj5uPdL8U='") {
        stage('prepare') {
            echo 'preparing...'
            sh 'env'
            sh "bash ./speed -s $SPEED composer"
            sh 'composer install'
            echo 'prepare done.'
        }
        stage('test') {
            echo 'testing...'
            sh './lint.sh'
            sh './phpunit.sh'
            junit 'junit.xml'
            echo 'test done.'
        }
    }
    stage('deploy') {
        if(GIT_LOCAL_BRANCH != '6.x') {
            echo 'do nothing.'
            return
        }
        echo 'build docker for production'
        imageName = "${DOCKER_SERVER}${DOCKER_PATH}:latest"
        docker.build(imageName, "--build-arg APP_ENV=production --build-arg SPEED=$SPEED ./")
        docker.withRegistry("https://${DOCKER_SERVER}", $CODING_ARTIFACTS_CREDENTIALS_ID) {
            docker.image(imageName).push()
        }

        echo 'deploy docker image to server'
        withCredentials([usernamePassword(credentialsId: env.CODING_ARTIFACTS_CREDENTIALS_ID, usernameVariable: 'DOCKER_USER', passwordVariable: 'DOCKER_PASSWORD')]) {
            dockerUser = DOCKER_USER
            dockerPassword = DOCKER_PASSWORD
        }
        withCredentials([sshUserPrivateKey(credentialsId: "${WEB_SERVER_CREDENTIALS_ID}", keyFileVariable: 'id_rsa')]) {
            remote.identityFile = id_rsa
            remote.host = $WEB_SERVER_HOST
            remote.user = $WEB_SERVER_USER
            sshCommand remote: remote, command: "docker login -u ${dockerUser} -p ${dockerPassword} $DOCKER_SERVER"
            sshCommand remote: remote, command: "docker pull ${imageName}"
            sshCommand remote: remote, command: "docker stop web && docker rm web"
            sshCommand remote: remote, command: "docker run --name web -d -e 'DB_CONNECTION=sqlite' -e 'APP_KEY=${APP_KEY}' ${imageName}"
        }
        echo 'deploy done.'
    }
}
